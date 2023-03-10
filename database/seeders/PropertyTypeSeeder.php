<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PropertyType;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            "Single Family",
            "2-4 Unit Multifamily",
            "5-Unit Multifamily",
            "6-Unit Multifamily",
            "7-Unit Multifamily",
            "8-Unit Multifamily",
            "9-Unit Multifamily",
            "10-Unit Multifamily",
            "Mixed Use with  Residential & Retail",
            "Mixed Use with  Residential & Shop Front",
            "Mixed Use with  Residential & Warehouse",
            "Mixed Use with  Residential & Garage",
            "Mixed Use with  Residential & Office",
            "Mixed Use with  Residential & Storage Space",
            "Mixed Use with  Residential & Industrial",
            "Mixed Use with  Office & Industrial",
            "Mixed Use with Industrial & Commercial",
            "Industr Vacant Land",
            "Hotel",
            "Motel",
            "Inn - Resort",
            "Time Share",
            "Nursing Home",
            "Private Hospital",
            "Care/Treatmt Facility",
            "Laundry/Laundromat",
            "Fuel Tank",
            "Bottled/Propane Gas Tanks",
            "Grain/Feed Elevator",
            "Lumber Yard",
            "Trucking Terminal",
            "Pier-Dock",
            "Warehouse - Commercial",
            "Farm Building",
            "Commercial Greenhouse",
            "Retail - Equipment",
            "Department Store",
            "Shopping Ctr/Mall",
            "Supermarket",
            "Retail - Service",
            "Restaurant/Bar",
            "Retail Any",
            "Auto Sales",
            "Auto Supply",
            "Auto Repair",
            "Gas Station",
            "Gas/Service Station",
            "Car Wash Facility",
            "Parking Garage",
            "Parking Lot",
            "Auto - Other",
            "Car Wash - Self",
            "Office Bldg - General",
            "Bank Building",
            "Office Bldg - Medical",
            "Medical Facility",
            "Commercial Building",
            "Post Office",
            "Educational Property",
            "Day Care Center",
            "Bus Facility",
            "Funeral Home",
            "Public Service - Misc",
            "Commercial Condo Parking",
            "Museum",
            "Art Gallery",
            "Movie Theater",
            "Drive-In Movies",
            "Live Theater",
            "Stadium",
            "Arena & Field House",
            "Race Track",
            "Amusement Park",
            "Entertainment - Other",
            "Bowling Alley",
            "Ice Skating",
            "Roller Skating",
            "Swimming Pools",
            "Health Spa",
            "Tennis/Racquetball Club",
            "Gymnasium",
            "Indoor Recreation",
            "Recreation",
            "Golf Course",
            "Tennis Courts",
            "Riding Stables",
            "Beach Or Swimming Pool",
            "Marina",
            "Fish & Game Club",
            "Camping Facility",
            "Summer Camp",
            "Outdoor Recreation",
            "Hangar Storage",
            "Commercial Use",
            "Manufacturing Building",
            "Warehouse - Industrial",
            "Office Building - Industrial",
            "Research & Dvlpmnt Facility",
            "Industrial Building",
            "Sand Quarry",
            "Gypsum Quarry",
            "Rock Quarry",
            "Other Quarry",
            "Bottling Plant",
            "Public Utility",
            "Public Utility Tanks",
            "Public Utility Tank, Lng",
            "Electric Plant",
            "Electric Right Of Way",
            "Electric Substation",
            "Gas Production Plant",
            "Gas Pipeline Right Of Way",
            "Gas Storage",
            "Gas Control Station",
            "Telephone Exchange Station",
            "Telephone Relay Tower",
            "Cable Television Transmssn",
            "Radio Television Transmssn",
            "Studio Remote",
            "Indus Condo",
            "Industrial Use",
            "Farm And Forest Use",
            "Farm Use",
            "Recreational General",
            "Golfing Area",
            "Horseback Riding",
            "Hunting Area",
            "Fishing Area",
            "Alpine Skiing Area",
            "Alpine Skiing Area",
            "Swimming Area",
            "Picnic Area",
            "Public Flying Area",
            "Target Range",
            "Gun Club",
            "Other Fish/Game Club",
            "Condominium"
        ];
        foreach ($types as $type) {
            PropertyType::create([
                "type" => $type
            ]);
        }
    }
}
