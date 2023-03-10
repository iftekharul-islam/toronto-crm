<?php

namespace App\Http\Controllers;

use Zip;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\CallLog;
use App\Models\OrderWQa;
use App\Models\OrderWcom;
use App\Helpers\CrmHelper;
use App\Models\OrderWReport;
use App\Traits\GlobalHelper;
use Illuminate\Http\Request;
use App\Models\OrderWRewrite;
use Illuminate\Http\Response;
use App\Models\OrderWRevision;
use App\Models\OrderWInspection;
use App\Models\OrderWSubmission;
use Spatie\GoogleCalendar\Event;
use Illuminate\Http\JsonResponse;
use App\Models\OrderWInitialReview;
use App\Models\OrderWReportAnalysis;
use App\Repositories\OrderRepository;
use App\Services\OrderWorkflowService;
use App\Repositories\OrderWorkflowRepository;


class OrderWorkflowController extends BaseController
{
    protected OrderWorkflowService $service;
    protected OrderWorkflowRepository $repository;
    protected OrderRepository $orderRepository;
    use CrmHelper, GlobalHelper;

    protected $workFlowStatus = 0;

    public function __construct(OrderWorkflowService $order_w_service, OrderWorkflowRepository $order_w_repository, OrderRepository $order_repository)
    {
        parent::__construct();
        $this->service = $order_w_service;
        $this->repository = $order_w_repository;
        $this->orderRepository = $order_repository;
    }

    public function updateOrderSchedule(Request $request)
    {
        $this->repository->updateOrderScheduleData($request->all());
        //code for set event on google calender
        if (config('app.env') == "production") {
            $this->service->setOrderSchedule($request->order_id);
        }
        // inspection_date_time_formatted

        $order = Order::find($request->order_id);
        $user = auth()->user();
        if ($request->schedule_id > 0) {
            $message = 'Re Scheduled successfully';
            $historyTitle = auth()->user()->name . " Re scheduled the order. <br>Order Client No: <strong class='text-primary'>{$order->client_order_no}</strong><br>Schedule Time: <strong class='text-danger'>{$request->inspection_date_time}</strong><br>Note: <strong class='text-primary'>{$request->note}</strong><br>Reschedule Note: <strong class='text-primary'>{$request->reschedule_note}</strong><br>Duration: <strong>{$request->duration}</strong>";
        } else {
            $message = 'Schedule createded successfully';
            $historyTitle = auth()->user()->name . " scheduled the order. <br>Order Client No: <strong class='text-primary'>{$order->client_order_no}</strong><br>Schedule Time: <strong class='text-danger'>{$request->inspection_date_time}</strong><br>Note: <strong class='text-primary'>{$request->note}</strong><br>Duration: <strong>{$request->duration}</strong>";
        }

        $this->addHistory($order, $user, $historyTitle, 'scheduling');

        $orderData = $this->orderDetails($request->order_id);
        $filterValue = $this->orderCounter();

        $appraisers = $this->orderRepository->getUserExpectRole(role: 'admin');
        $companyId = $user->getCompanyProfile()->company_id;
        $data = '';
        $paginate = 10;
        $dateRange = '';
        $filterType = $request->filter ?? 'all';
        $order = $this->orderDataDetails($data, $companyId, $paginate, $dateRange, $filterType);
        return [
            'error' => false,
            'message' => $message,
            'data' => $orderData,
            'filterValue' => $filterValue,
            'orderDetails' => $order
        ];
    }


    public function deleteSchedule(Request $request, $id)
    {
        $order_w_schedule = OrderWInspection::find($id);
        $order = Order::find($order_w_schedule->order_id);
        $order->forceFill([
            'workflow_status->scheduling' => 0,
            'status' => 0
        ])->save();
        $user = auth()->user();
        $historyTitle = auth()->user()->name . " delete the schedule. <br>Order Client No: <strong class='text-primary'>{$order->client_order_no}</strong><br>Delete note: <strong class='text-danger'>{$request->delete_note}</strong>";

        $data = [
            "activity_text" => "Schedule Deleted By " . $user->name . " REASON: " . $request->delete_note,
            "activity_by" => auth()->user()->id,
            "order_id" => $order_w_schedule->order_id
        ];

        $this->orderRepository->addActivity($data);
        $this->addHistory($order, $user, $historyTitle, 'scheduling');
        $orderData = $this->orderDetails($order->id);

        if (config('app.env') == "production") {
            $this->service->deleteOrderSchedule($id);
        }

        $this->repository->deleteSchedule($id);
        $filterValue = $this->orderCounter();

        $appraisers = $this->orderRepository->getUserExpectRole(role: 'admin');
        $companyId = $user->getCompanyProfile()->company_id;
        $data = '';
        $paginate = 10;
        $dateRange = '';
        $filterType = $request->filter ?? 'all';
        $order = $this->orderDataDetails($data, $companyId, $paginate, $dateRange, $filterType);
        return [
            'error' => false,
            'message' => "Schedule deleted successfully",
            'data' => $orderData,
            'filterValue' => $filterValue,
            'orderDetails' => $order
        ];
    }


    public function uploadInspectionFiles(Request $request, $inspection_id)
    {
        $order_w_inspection = OrderWInspection::find($inspection_id);
        if (!$order_w_inspection) {
            return response([
                "error" => true,
                "message" => "Please Create schedule first ! No schedule is set."
            ]);
        }
        $data = $this->saveInspectionFiles($request->all(), $inspection_id);
        $order = Order::find($order_w_inspection->order_id);
        $user = auth()->user();

        $historyTitle = auth()->user()->name . ' upload the inspection file';
        $this->addHistory($order, $user, $historyTitle, 'inspection');

        $order->forceFill([
            'workflow_status->inspection' => 1,
            'status' => 3
        ])->save();

        $orderData = $this->orderDetails($order->id);

        return [
            "file" => $data['media'],
            "data" => $orderData,
            'error' => false,
            "message" => "Inspection file uploaded successfully"
        ];
    }

    public function saveInspectionFiles($data, $inspection_id)
    {
        $inspection = OrderWInspection::find($inspection_id);
        if (!$inspection) {
            return false;
        }
        $zip = new \ZipArchive();
        $date = date('d-M-Y-H-i-s');
        $fileName = 'inspection-files(' . $date . ').zip';
        foreach ($data['files'] as $key => $file) {
            if ($zip->open(public_path($fileName), \ZipArchive::CREATE) == TRUE) {
                $relativeName = ++$key . '.' . $file->getClientOriginalExtension();
                $zip->addFile($file->getPathName(), $relativeName);
            }
        }
        $zip->close();

        $inspection->find($inspection_id)->addMedia($fileName)->toMediaCollection('inspection');
        $inspection = OrderWInspection::with('attachments')->where('id', $inspection_id)->first();

        $historyTitle = auth()->user()->name . " has saved order Inspection File.<br>File is: " . $inspection->attachments;

        return [
            'error' => false,
            'message' => $historyTitle,
            'status' => true,
            'media' => $inspection->attachments,
        ];
    }

    public function storeAdminReportPreparation(Request $request, $id)
    {
        $order = Order::find($id);
        $user = auth()->user();
        $reviewer_name = '';

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Order Information Not Found'
            ]);
        }
        $report = OrderWReport::where('order_id', $id)->first();
        $creator = User::find($request->creator_id);
        if($request->has('reviewed_by') && $request->reviewed_by != ''){
            $reviewer = User::find($request->reviewed_by);
            $reviewer_name = $reviewer->name;
        }

        if ($report) {
            if($request->has('reviewed_by') && $request->reviewed_by != ''){
                $report->reviewed_by = $request->reviewed_by;
            }
            $report->creator_id = $request->creator_id;
            $report->save();
            $historyTitle = $user->name . " update report creator and viewer on report preperation.<br>Creator: <strong>{$creator->name}</strong><br>Reviewer: <strong>{$reviewer_name}</strong>";
        } else {
            $newReport = new OrderWReport();
            $newReport->order_id = $id;
            if($request->has('reviewed_by') && $request->reviewed_by != ''){
                $newReport->reviewed_by = $request->reviewed_by;
            }
            $newReport->creator_id = $request->creator_id;
            $newReport->created_by = $user->id;
            $newReport->save();
            $historyTitle = $user->name . " assign report creator and viewer on report preperation.<br>Creator: <strong>{$creator->name}</strong><br>Reviewer: <strong>{$reviewer_name}</strong>";
        }

        $workStatus = json_decode($order->workflow_status, true);
        $workStatus['reportPreparation'] = 1;

        $order->status = 4;
        $order->workflow_status = json_encode($workStatus);
        $order->save();

        $this->addHistory($order, $user, $historyTitle, 'report-preparation');
        $orderData = $this->orderDetails($id);
        return [
            'error' => false,
            'message' => "Report preperation has been updated",
            'status' => 'success',
            'data' => $orderData
        ];
    }

    public function storeAssigneeReportPreparation(Request $request, $id)
    {
        $order = Order::find($id);
        $user = auth()->user();

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Order Information Not Found'
            ]);
        }

        $report = OrderWReport::where('order_id', $id)->first();
        $assignee = User::find($request->assigned_to);
        $trainee = User::find($request->trainee_id);

        if ($report) {
            if($request->has('reviewed_by') && $request->reviewed_by != ''){
                $report->reviewed_by = $request->reviewed_by;
            }
            $report->creator_id = $request->creator_id;
            $report->assigned_to = $request->assigned_to;
            $report->trainee_id = $request->trainee_id;
            if ($report->note != $request->note) {
                $report->note_time = now()->toDateTimeString();
            }
            $report->note = $request->note;
            $report->updated_by = auth()->user()->id;
            $report->save();

            if (isset($request['files']) && count($request['files'])) {
                $this->savePreparationFiles($request->all(), $report->id);
            }
            $historyTitle = $user->name . " update assign and trainee selection on report preperation.<br>Assign To: <strong>{$assignee->name}</strong><br>Trainee: <strong>{$trainee->name}</strong><br>Note: <strong>{$request->note}</strong>";
        } else {
            $newReport = new OrderWReport();
            $newReport->order_id = $order->id;
            if($request->has('reviewed_by') && $request->reviewed_by != ''){
                $newReport->reviewed_by = $request->reviewed_by;
            }
            $newReport->creator_id = $request->creator_id;
            $newReport->assigned_to = $request->assigned_to;
            $newReport->trainee_id = $request->trainee_id;
            $newReport->note = $request->note;
            $newReport->note_time = now()->toDateTimeString();
            $newReport->created_by = $user->id;
            $newReport->save();
            $historyTitle = $user->name . " set assign and trainee selection on report preperation.<br>Assign To: <strong>{$assignee->name}</strong><br>Trainee: <strong>{$trainee->name}</strong><br>Note: <strong>{$request->note}</strong>";

            if (isset($request['files']) && count($request['files'])) {
                $this->savePreparationFiles($request->all(), $newReport->id);
            }
        }
        $workStatus = json_decode($order->workflow_status, true);
        $workStatus['reportPreparation'] = 1;

        $orderWreportAnalysis = OrderWInitialReview::where('order_id', $order->id)->first();
        if ($orderWreportAnalysis && $orderWreportAnalysis->is_check_upload == "1") {
            $order->status = 9;
        } else {
            $order->status = 4;
        }

        $order->workflow_status = json_encode($workStatus);
        $order->save();

        $this->addHistory($order, $user, $historyTitle, 'report-preparation');
        $orderData = $this->orderDetails($id);
        return [
            'error' => false,
            'message' => "New assignee has been updated",
            'status' => 'success',
            'data' => $orderData
        ];
    }

    public function savePreparationFiles($data, $id)
    {
        $report = OrderWReport::find($id);
        if (!$report) {
            return false;
        }
        foreach ($data['files'] as $file) {
            $report->find($id)->addMedia($file)
                ->withCustomProperties(['type' => $data['file_type']])
                ->toMediaCollection('preparation');
        }

        return true;
    }

    public function storeReportAnalysis(Request $request, $id)
    {
        $order = Order::find($id);
        $user = auth()->user();

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Order Information Not Found'
            ]);
        }

        $analysis = OrderWReportAnalysis::where('order_id', $id)->first();
        $this->workFlowStatus = 9;
        if ($analysis) {
            $noteCheckOrNot = "Checked as check and upload";

            if ($request->noteCheck == '1') {
                $analysis->is_review_send_back = 1;
                $analysis->is_check_upload = 0;
                if ($analysis->rewrite_note != $request->note) {
                    $analysis->note_time = now()->toDateTimeString();
                }
                $analysis->rewrite_note = $request->note ?? "";
                $noteCheckOrNot = "Checked as rewrite and send back";
                $this->workFlowStatus = 7;
            } else {
                $analysis->is_review_send_back = 0;
                $analysis->is_check_upload = 1;
                if ($analysis->note != $request->note) {
                    $analysis->note_time = now()->toDateTimeString();
                }
                $analysis->note = $request->note ?? "";
                $this->workFlowStatus = 6;
            }
            $analysis->updated_by = $user->id;
            $analysis->save();

            if (isset($request['files']) && count($request['files'])) {
                $this->saveAnalysisFiles($request->all(), $analysis->id);
            }
            $historyTitle = $user->name . " update report analysis. {$noteCheckOrNot}.<br>Note: <strong class='text-primary'>{$request->note}</strong>";
        } else {
            $newAnalysis = new OrderWReportAnalysis();
            $newAnalysis->order_id = $id;
            $newAnalysis->assigned_to = $request->assigned_to;
            $newAnalysis->created_by = $user->id;
            $newAnalysis->updated_by = $user->id;
            $newAnalysis->save();

            $assigne = User::find($request->assigned_to);
            $historyTitle = $user->name . " set assign to report analysis.<br>Assign To: <strong>{$assigne->name}</strong>";
        }

        $workStatus = json_decode($order->workflow_status, true);
        $workStatus['reportAnalysisReview'] = 1;
        $order->status = $this->workFlowStatus;
        $order->workflow_status = json_encode($workStatus);
        $order->save();

        $this->addHistory($order, $user, $historyTitle, 'report-analysis-review');
        $orderData = $this->orderDetails($id);
        return [
            'error' => false,
            'message' => "Update successfull report analysis & review",
            'data' => $orderData
        ];
    }

    public function saveRewriteFiles($data, $id)
    {
        $rewrite = OrderWRewrite::find($id);
        if (!$rewrite) {
            return false;
        }
        foreach ($data['files'] as $file) {
            $rewrite->find($id)->addMedia($file)
                ->toMediaCollection('report-rewrite');
        }
        return true;
    }

    public function saveAnalysisFiles($data, $id)
    {
        $analysis = OrderWReportAnalysis::find($id);
        if (!$analysis) {
            return false;
        }
        foreach ($data['files'] as $file) {
            $analysis->find($id)->addMedia($file)
                ->withCustomProperties(['type' => $data['file_type']])
                ->toMediaCollection('analysis');
        }
        return true;
    }

    public function saveInitialReview(Request $request)
    {
        $order = Order::find($request->order_id);
        $this->repository->updateInitialReviewData($request->all());
        $user = auth()->user();
        $assignee = User::find($request->assigned_to);
        if ($request->initial_review_id > 0) {
            $message = 'Initial Review updated successfully';
            $historyTitle = auth()->user()->name . ' set assign and update notes on Initial review.<br>Note: <strong>' . $request->note . '</strong><br>Assign To: <strong>' . $assignee->name . '</strong>';
        } else {
            $message = 'Initial Review createded successfully';
            $historyTitle = auth()->user()->name . ' set assign and update notes on Initial review.<br>Note: <strong>' . $request->note . '</strong><br>Assign To: <strong>' . $assignee->name . '</strong>';
        }

        $this->addHistory($order, $user, $historyTitle, 'initial-review');
        $chexbox = $request->checkbox;
        $orderReportPreperation = OrderWReport::where("order_id", $request->order_id)->first();
        if ($chexbox == 1) {
            $order->status = 4;
        } else if ($chexbox == 2 && $orderReportPreperation) {
            $order->status = 9;
        }
        $order->save();

        $orderData = $this->orderDetails($request->order_id);
        return [
            'error' => false,
            'message' => $message,
            'data' => $orderData
        ];
    }

    public function saveQualityAssurance(Request $request)
    {
        $this->repository->saveQualityAssurance($request->all());

        $order = Order::find($request->order_id);
        $user = auth()->user();

        $assignee = User::find($request->assigned_to);
        $effectiveDate = $request->effective_date;

        if ($request->qa_id > 0) {
            $message = 'Quality Assurance updated successfully';
            $historyTitle = auth()->user()->name . ' set assign on Quality Assurance.<br>Assign To: <strong>' . $assignee->name . '</strong><br>Effective Date: <strong>' . $effectiveDate . '</strong>';
        } else {
            $message = 'Quality Assurance createded successfully';
            $historyTitle = auth()->user()->name . ' set assign on Quality Assurance.<br>Assign To: <strong>' . $assignee->name . '</strong><br>Effective Date: <strong>' . $effectiveDate . '</strong>';
        }

        $this->addHistory($order, $user, $historyTitle, 'QA-section');

        $order->forceFill([
            'workflow_status->qualityAssurance' => 1,
            'status' => 10
        ])->save();

        $orderData = $this->orderDetails($request->order_id);
        return [
            'error' => false,
            'message' => $message,
            'data' => $orderData
        ];
    }

    public function updateQualityAssurance(Request $request)
    {
        $this->repository->updateQualityAssurance($request->all());
        $order_qa = OrderWQa::find($request->qa_id);

        if (!$order_qa) {
            return response()->json([
                'error' => true,
                'message' => "There are no existing qa, or please reload once"
            ]);
        }

        $order = Order::find($order_qa->order_id);
        $user = auth()->user();
        if ($request->qa_id > 0) {
            $message = 'Quality Assurance updated successfully';
            $historyTitle = auth()->user()->name . ' changes on Quality Assurance.<br>Note: <strong>' . $request->note;
        } else {
            $message = 'Quality Assurance createded successfully';
            $historyTitle = auth()->user()->name . ' changes on Quality Assurance.<br>Note: <strong>' . $request->note;
        }

        $this->addHistory($order, $user, $historyTitle, 'QA-section');

        $order->forceFill([
            'workflow_status->qualityAssurance' => 1,
            'status' => 5
        ])->save();

        $orderData = $this->orderDetails($order->id);
        return [
            'error' => false,
            'message' => $message,
            'data' => $orderData
        ];
    }

    public function rewriteReport(Request $get)
    {
        $order = Order::find($get->order_id);
        $user = auth()->user();

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Order Information Not Found'
            ]);
        }

        $reWrite = OrderWRewrite::where('order_id', $order->id)->first();
        if (!$reWrite) {
            $reWrite = new OrderWRewrite();
            $reWrite->order_id = $order->id;
            $reWrite->note = $get->note ?? "";
            $reWrite->created_at = Carbon::now();
            $reWrite->created_by = $user->id;
            $reWrite->save();
        } else {
            $reWrite->updated_by = $user->id;
            $reWrite->updated_at = Carbon::now();
        }
        if ($reWrite->note != $get->note) {
            $reWrite->note_time = now()->toDateTimeString();
        }
        $reWrite->note = $get->note ?? "";
        $reWrite->save();

        $historyTitle = $user->name . " has update Re-writing the report.<br>Current note is <strong>" . $get->note . "</strong>";

        if (isset($get['files']) && count($get['files'])) {
            $this->saveRewriteFiles($get->all(), $reWrite->id);
        }

        $workStatus = json_decode($order->workflow_status, true);
        $workStatus['reWritingReport'] = 1;

        $order->status = 9;
        $order->workflow_status = json_encode($workStatus);
        $order->save();

        $this->addHistory($order, $user, $historyTitle, 'rewriting-report');
        $orderData = $this->orderDetails($get->order_id);

        return [
            'error' => false,
            'message' => "Re-writing the report updated successful",
            'status' => 'success',
            'data' => $orderData
        ];
    }


    public function rewriteReportAssignee(Request $get)
    {
        $order = Order::find($get->orderId);
        $user = auth()->user();

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Order Information Not Found'
            ]);
        }

        $reWrite = OrderWRewrite::where('order_id', $order->id)->first();
        $assignee = User::find($get->assigned_to);
        $error = false;
        if (!$reWrite) {
            $reWrite = new OrderWRewrite();
            $reWrite->order_id = $order->id;
            $reWrite->created_at = Carbon::now();
            $reWrite->created_by = $user->id;
            $reWrite->assigned_to = $get->assigned_to;
            $reWrite->save();
            $historyTitle = "<strong>{$assignee->name}</strong> has assiged by " . $user->name . ' on the Re-writing the report Assign.';
            $this->addHistory($order, $user, $historyTitle, 'rewriting-report');
        } else {
            // $error = true;
            $historyTitle = "Trying to reassign on Re-writing the report. Already assigned, can't reassign now.";
        }

        $analysis = OrderWReportAnalysis::where('order_id', $order->id)->first();
        if ($analysis && $analysis->is_review_send_back == 1) {
            $order->status = 8;
            $order->save();
        }

        $orderData = $this->orderDetails($get->orderId);
        return [
            'error' => $error,
            'message' => $historyTitle,
            'status' => 'success',
            'data' => $orderData
        ];
    }

    public function revissinAdd(Request $get)
    {
        $order = Order::find($get->order_id);
        $user = auth()->user();

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Order Information Not Found'
            ]);
        }

        $formated_date_time = \DateTime::createFromFormat('D M d Y H:i:s e+', $get->date);
        $deliveredDate = Carbon::parse($formated_date_time)->format('Y-m-d H:i:s');

        $reWrite = new OrderWRevision();
        $reWrite->order_id = $order->id;
        $reWrite->created_at = Carbon::now();
        $reWrite->created_by = $user->id;
        $reWrite->updated_by = $user->id;
        $reWrite->revision_date = $deliveredDate;
        $reWrite->delivery_date = Carbon::now();
        $reWrite->revision_details = $get->revission;
        $reWrite->solution_details = "-";
        $reWrite->save();
        $historyTitle = $user->name . " create a new revission.<br>Revission is: <strong>{$get->revission}</strong><br>Revission date: <strong>{$deliveredDate}</strong>";

        $workStatus = json_decode($order->workflow_status, true);
        $workStatus['revision'] = 1;

        $order->status = 12;
        $order->workflow_status = json_encode($workStatus);
        $order->save();

        $this->addHistory($order, $user, $historyTitle, 'revision');
        $orderData = $this->orderDetails($get->order_id);
        return [
            'error' => false,
            'message' => "New revission has been added",
            'status' => 'success',
            'data' => $orderData
        ];
    }

    public function revissinEdit(Request $get)
    {
        $order = Order::find($get->order_id);
        $user = auth()->user();

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Order Information Not Found'
            ]);
        }

        $formated_date_time = \DateTime::createFromFormat('D M d Y H:i:s e+', $get->date);
        $deliveredDate = Carbon::parse($formated_date_time)->format('Y-m-d H:i:s');

        $reWrite = OrderWRevision::where('order_id', $get->order_id)->where('id', $get->id)->first();
        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Order Revission Information Not Found'
            ]);
        }
        $previousRev = $reWrite->revision_details;

        $reWrite->updated_at = Carbon::now();
        $reWrite->updated_by = $user->id;
        $reWrite->revision_date = $deliveredDate;
        $reWrite->revision_details = $get->revission;
        $reWrite->solution_details = "-";
        $reWrite->save();

        $historyTitle = $user->name . " update revission.<br>Prev Revission was: <strong class='text-danger'>{$previousRev}</strong><br>Current Revission: <strong class='text-primary'>{$get->revission}</strong>";

        $workStatus = json_decode($order->workflow_status, true);
        $workStatus['revision'] = 1;

        $order->status = 12;
        $order->workflow_status = json_encode($workStatus);
        $order->save();

        $this->addHistory($order, $user, $historyTitle, 'revision');
        $orderData = $this->orderDetails($get->order_id);
        return [
            'error' => false,
            'message' => "Revission has been edited",
            'status' => 'success',
            'data' => $orderData
        ];
    }

    public function revissinSolutionAdd(Request $get)
    {
        $order = Order::find($get->order_id);
        $user = auth()->user();

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Order Information Not Found'
            ]);
        }

        $reWrite = OrderWRevision::where('order_id', $get->order_id)->where('id', $get->revission['id'])->first();
        if (!$reWrite) {
            return response()->json([
                'error' => true,
                'message' => 'Order Revision Information Not Found'
            ]);
        }
        $reWrite->updated_at = Carbon::now();
        $reWrite->updated_by = $user->id;
        $reWrite->completed_by = $user->id;
        $reWrite->solution_details = $get->revission['solution_details_edited'];
        $reWrite->save();

        $deliveryDate = date('d-m-Y', strtotime($reWrite->delivery_date));
        $historyTitle = $user->name . " add a solution.<br>Revission: <strong class='text-info'>{$reWrite->revision_details}</strong> <br>Solution: <strong class='text-success'>{$get->revission['solution_details_edited']}</strong>";

        $workStatus = json_decode($order->workflow_status, true);
        $workStatus['revision'] = 1;

        // $order->status = 13;
        $order->workflow_status = json_encode($workStatus);
        $order->save();

        $this->addHistory($order, $user, $historyTitle, 'revision');
        $orderData = $this->orderDetails($get->order_id);

        return [
            'error' => false,
            'message' => "Solution added successfully",
            'status' => 'success',
            'data' => $orderData
        ];
    }

    public function revissinSolutionMarked(Request $get)
    {
        $order = Order::find($get->order_id);
        $user = auth()->user();

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Order Information Not Found'
            ]);
        }

        $reWrite = OrderWRevision::where('order_id', $get->order_id)->where('id', $get->id)->first();
        if (!$reWrite) {
            return response()->json([
                'error' => true,
                'message' => 'Order Revision Information Not Found'
            ]);
        }
        $reWrite->updated_at = Carbon::now();
        $reWrite->updated_by = $user->id;
        $reWrite->completed_by = $get->completed_by;
        $reWrite->status = 1;
        $reWrite->delivered_by = $get->delivered_by;
        $reWrite->delivery_date = Carbon::parse($get->delivery_date);
        $reWrite->solution_details = $get->solution_details;
        $reWrite->save();

        $deliveryDate = date('d-m-Y', strtotime($reWrite->delivery_date));
        $completedUser = User::find($get->completed_by);
        $deliveredUser = User::find($get->delivered_by);
        $historyTitle = $user->name . " marked as delivery for revission.<br>Revission: <strong class='text-info'>{$reWrite->revision_details}</strong> <br>Solution: <strong class='text-success'>{$reWrite->solution_details}</strong><br>Completed By: <strong class='text-info'>{$completedUser->name}</strong><br>Delivered By: <strong class='text-primary'>{$deliveredUser->name}</strong><br>Delivery Date: <strong class='text-primary'>{$deliveryDate}</strong>";


        $workStatus = json_decode($order->workflow_status, true);
        $workStatus['revision'] = 1;

        $reWriteAll = OrderWRevision::where('order_id', $get->order_id)->get();
        $allDone = true;

        foreach($reWriteAll as $reItem) {
            if ($reItem->status == 0) {
                $allDone = false;
                break;
            }
        }

        if ($allDone) {
            $order->status = 13;
        }
        $order->workflow_status = json_encode($workStatus);
        $order->save();

        $this->addHistory($order, $user, $historyTitle, 'revision');
        $orderData = $this->orderDetails($get->order_id);

        return [
            'error' => false,
            'message' => "Revission has been marked as solution",
            'status' => 'success',
            'data' => $orderData
        ];
    }

    public function revissinSolutionDelete(Request $get)
    {
        $order = Order::find($get->order_id);
        $user = auth()->user();

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Order Information Not Found'
            ]);
        }

        $reWrite = OrderWRevision::where('order_id', $get->order_id)->where('id', $get->id)->first();
        if (!$reWrite) {
            return response()->json([
                'error' => true,
                'message' => 'Order Revision Information Not Found'
            ]);
        }
        $historyTitle = "Revision has been deleted by " . $user->name . "<br>Solution: <strong class='text-danger'>{$reWrite->solution_details}</strong>";
        $reWrite->delete();

        $this->addHistory($order, $user, $historyTitle, 'revision');
        $orderData = $this->orderDetails($get->order_id);

        $reWrite = OrderWRevision::where('order_id', $get->order_id)->first();
        if (!$reWrite) {
            $workStatus = json_decode($order->workflow_status, true);
            $workStatus['revision'] = 0;
            $order->workflow_status = json_encode($workStatus);
            $order->save();
        }

        return [
            'error' => false,
            'message' => "Revission has been deleted",
            'status' => 'success',
            'data' => $orderData
        ];
    }

    public function storeSubmission(Request $request, $id)
    {
        $order = Order::find($id);
        $user = auth()->user();

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Order Information Not Found'
            ]);
        }

        $submission = OrderWSubmission::where('order_id', $id)->first();
        if ($submission) {
            $submission->trainee_id = $request->trainee_id;
            $submission->delivery_man_id = $request->delivery_man_id;
            $submission->delivery_date = $request->delivery_date;
            $submission->is_trainee_signed = $request->is_trainee_signed ? 1 : 0;
            $submission->updated_by = auth()->user()->id;
            $submission->save();

            $historyTitle = "Workflow submission updated data by " . $user->name . ' on Workflow submission section..<br>Delivery date: '.$request->delivery_date;
        } else {
            $newSubmission = new OrderWSubmission();
            $newSubmission->order_id = $order->id;
            $newSubmission->trainee_id = $request->trainee_id;
            $newSubmission->delivery_man_id = $request->delivery_man_id;
            $newSubmission->delivery_date = $request->delivery_date;
            $newSubmission->is_trainee_signed = $request->is_trainee_signed ? 1 : 0;
            $newSubmission->created_by = auth()->user()->id;
            $newSubmission->save();

            $historyTitle = "Workflow submission created data by " . $user->name . ' on Workflow submission section..<br>Delivery date: '.$request->delivery_date;
        }

        $workStatus = json_decode($order->workflow_status, true);
        $workStatus['submission'] = 1;
        $order->status = 11;
        $order->workflow_status = json_encode($workStatus);
        $order->save();

        $this->addHistory($order, $user, $historyTitle, 'submission');
        $orderData = $this->orderDetails($id);
        return [
            'error' => false,
            'message' => "Submission has been done",
            'status' => 'success',
            'data' => $orderData
        ];
    }

    public function saveCom(Request $request, $id)
    {
        $this->repository->saveCom($request->all(), $id);
        $orderData = $this->orderDetails($id);

        $order = Order::find($id);
        $user = auth()->user();
        $historyTitle = "Com list added by " . auth()->user()->name;
        $this->addHistory($order, $user, $historyTitle, 'QA-section');
        return [
            "message" => "Com list added successfully",
            "data" => $orderData
        ];
    }

    public function saveComRoute(Request $request)
    {
        $this->repository->saveComRoute($request->all());
        $order_id = $request->order_id;
        $orderData = $this->orderDetails($order_id);

        $order = Order::find($order_id);
        $user = auth()->user();
        $historyTitle = "Com list route mapped by " . auth()->user()->name;
        $this->addHistory($order, $user, $historyTitle, 'QA-section');
        return [
            "message" => "Com list route mapped successfully",
            "data" => $orderData
        ];
    }

    public function publicCom($order_id)
    {
        $order = Order::find(base64_decode($order_id));
        if (!$order) {
            abort(404);
        }
        $com_list = $this->repository->getCom($order->id);
        return view('order.public-com', compact('com_list', 'order'));
    }

    public function publicComFiles(Request $request, $id)
    {
        $order_id = base64_decode($id);
        if (count($request->all()) <= 1) {
            return redirect()
                ->to('public-com/' . base64_encode($order_id))
                ->with(['error' => 'Atleast one file requires among following property types !']);
        }
        $this->repository->saveComFiles($request->all(), $order_id);

        return redirect()
            ->to('public-com/' . base64_encode($order_id))
            ->with(['success' => 'Property files uploaded successfully']);
    }

    public function checkClientOrderNo(Request $get)
    {
        $old = Order::where('client_order_no', $get->client_no)->with('propertyInfo', 'providerService')->first();
        if ($old) {
            $address = $old->propertyInfo->full_addr;
            $provider = json_decode($old->providerService->appraiser_type_fee, true)[0];
            $fullMessage = "<div class='mt-2'>The order no. already exists. The address is <strong style='color:#ff4406'>$address</strong> and Appraisal type is <strong style='color:#ff4406'>{$provider['type']}</strong></div>";
            return response()->json(['find' => true, 'message' => $fullMessage]);
        } else {
            return response()->json(['find' => false]);
        }
    }

    public function orderDataDetails($data, $companyId, $paginate, $dateRange, $filterType) {
        $orderId = null;
        $dataPropertyClient = false;
        if ($filterType == "completed") {
            $orderId = CallLog::where('status', 1)->get()->pluck('order_id')->toArray();
        } else if($filterType == "today_call") {
            $orderId = OrderWInspection::whereDate('inspection_date_time', '=', date('Y-m-d'))->get()->pluck('order_id')->toArray();
        }

        if ($data) {
            $orderIds = PropertyInfo::where('formatedAddress', 'LIKE', "%$data%")->get()->pluck('order_id')->toArray();
            $clientIds = Client::where("name", "LIKE", "%$data%")->get()->pluck('id')->toArray();
            $getAmcIds = Order::whereIn('amc_id', $clientIds)->pluck('id')->toArray();
            $getLenderIds = Order::whereIn('lender_id', $clientIds)->pluck('id')->toArray();
            $mergeOrder = array_merge($getAmcIds, $getLenderIds);
            $orderIds = array_unique(array_merge($orderIds, $mergeOrder));
            $newOrders = $orderIds;
            if (count($newOrders) > 0) {
                $orderId = $newOrders;
                $dataPropertyClient = true;
            }
        }

        if ($dataPropertyClient == false && ($data == null || $data == "") && $filterType == null) {
            $filterType = "to_schedule";
        }


        $order = Order::where(function($qry) use ($data, $orderId, $filterType, $dataPropertyClient) {
            if ($data) {
                return $qry->when($orderId, function($qrys) use ($orderId){
                    return $qrys->whereIn('id', $orderId);
                })
                    ->orWhere(function($qry2) use ($data, $dataPropertyClient) {
                        if ($dataPropertyClient == false) {
                            return $qry2->where('client_order_no', "LIKE", "%$data%")
                                ->orWhere("system_order_no", "LIKE", "%$data%");
                        }
                    });
            } else {
                if ($orderId) {
                    $searchOrderId = $orderId;
                    return $qry->whereIn('id', $searchOrderId);
                } else if($filterType == "today_call") {
                    $searchOrderId = $orderId ?? [];
                    return $qry->whereIn('id', $searchOrderId);
                }
            }
        })
            ->when($dateRange, function($qry) use ($dateRange) {
                $start = $dateRange['start'];
                $end = $dateRange['end'];
                if ($start && $end) {
                    $startTime = Carbon::parse($start);
                    $endTime = Carbon::parse($end);
                    return $qry->whereDate('created_at', ">=", $startTime)->whereDate('created_at', "<=", $endTime);
                }
            })
            ->when($filterType, function($qry) use ($filterType) {
                if ($filterType == "to_schedule") {
                    return $qry->where("status", 0);
                } else if($filterType == "schedule") {
                    return $qry->where("status", 1);
                } else if($filterType == "today_call") {
                    return $qry->where("status", "<", 3);
                }
            })
            ->with($this->order_call_list_relation())
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->paginate($paginate);
        return $order;
    }
}
