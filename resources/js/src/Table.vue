<template>
    <div class="m-table" :bgcolor="bgcolor">
        <div class="m-table-content">
            <table class="onlydesktop table">
                <thead>
                    <th width="2%" v-if="showSelectOpt">
                        <m-checkbox ref="clickAllCheck" :color='bgcolor' @change="checkAll" noripple/>
                    </th>
                    <th width="5%" v-if="showSlOpt" class="text-center">
                        SL
                    </th>
                    <th @click="clickHeader(headItem)" v-for="headItem, hi in headerItem" :class="`text-align-${headItem.align}`" :key="hi">
                        <slot :name="`head_${headItem.item}`" v-bind:item="headItem">
                            <template v-if="headItem.imageHead">
                                <img :src="headItem.imageHeadSrc.src" :height="headItem.imageHeadSrc.height" :width="headItem.imageHeadSrc.width">
                            </template>
                            <template v-else>
                                {{ headItem.title | ucFirst }}
                            </template>
                        </slot>
                    </th>
                </thead>
                <tbody>
                    <template v-if="dataItem.length > 0">
                        <tr v-for="tdItem, ti in dataItem" :key="ti">
                            <td width="2%" v-if="showSelectOpt" class="lnh-1">
                                <m-checkbox :color='bgcolor' @change="chooseItem($event, tdItem)" v-model="tdItem.selected" noripple/>
                            </td>
                            <td width="5%" v-if="showSlOpt" class="text-center">
                                {{ ti+slStart }}
                            </td>
                            <td v-for="hItem, hk in headerItem" :key="hk" :class="`text-align-${hItem.aligntd} item-${hItem.item}`">
                                <slot :name="hItem.item" v-bind:item="tdItem">
                                    <div v-html="setItemDetail(tdItem, hItem.item)"></div>
                                </slot>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td :colspan="headerItem.length+2" v-if="showSelectOpt && showSlOpt" class="text-center">
                                <div v-html="noDataMessage"></div>
                            </td>
                            <td :colspan="headerItem.length+1" v-else-if="showSelectOpt || showSlOpt" class="text-center">
                                <div v-html="noDataMessage"></div>
                            </td>
                            <td :colspan="headerItem.length" v-else class="text-center">
                                <div v-html="noDataMessage"></div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <slot name="popup"></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: 'm-table',
    props: ['show-select', 'collapse', 'collapse-text', 'header', 'items', 'empty-message', 'index', 'bgcolor', 'show-sl', 'sl-start'],
    data: () => ({
        tableHeader: [],
        headerItem: [],
        mobileText: "name",
        checkAllModel: false,
        firstClick: 0,
        getIndex: null,
        showSelectOpt: false,
        showSlOpt: false,
        noDataMessage: "No more data found",
        allCheck: false,
        expand: {
            title: 'Expand All',
            color: 'primary'
        },
        selectedItems: [],
        dataItem: [],
        dataItemInfo: [],
    }),
    model: {
        prop: 'selectedValue',
        event: 'change',
    },
    created(){
        if(this.showSelect != null){
            this.showSelectOpt = true;
        }
        if(this.showSl != null){
            this.showSlOpt = true;
        }
        if(this.collapseText != null){
            this.mobileText = this.collapseText;
        }
        let expand = 0;
        $(document).on("click", ".expandLists", function(){
            if(expand == 0){
                $(".m-content-td-data").slideDown(100);
                expand = 1;
                $(this).text("Collapse All");
                $(this).attr("color", "danger");
                let icon = $(".m-table-control-body").find(".m-table-control-right i");
                icon.removeClass('mdi-arrow-down');
                icon.addClass('mdi-arrow-up');
            }else{
                $(".m-content-td-data").slideUp(100);
                expand = 0;
                $(this).text("Expand All");
                $(this).attr("color", "primary");
                let icon = $(".m-table-control-body").find(".m-table-control-right i");
                icon.addClass('mdi-arrow-down');
                icon.removeClass('mdi-arrow-up');
            }
        });
        this.loadManage();
    },
    methods: {
        loadManage(){
            if(this.header != null){
                this.tableHeader = [];
                this.tableHeader = this.header;
            }
            if(this.items != null){
                this.dataItemInfo = [];
                this.dataItemInfo = this.items;
            }
            if(this.emptyMessage != null){
                this.noDataMessage = this.emptyMessage;
            }
            if(this.index != null){
                this.getIndex = this.index;
            }
            let checkHead = this.tableHeader;
            if(checkHead){
                this.headerItem = [];
                checkHead.map(ele => {
                    let spl = ele.split('@');
                    let alignStr = 'left';
                    let alignStr2 = 'left';
                    let imageHead = false;
                    let imageHeadSrc = {src: null, height: null, width: null};
                    if(spl[2]){
                        alignStr = spl[2];
                    }
                    if(spl[3]){
                        alignStr2 = spl[3];
                    }
                    if (spl[4]){
                        imageHead = true;
                        let imageStr = spl[4].split("_");
                        imageHeadSrc.src = imageStr[0] ?? null;
                        imageHeadSrc.height = imageStr[1] ?? null;
                        imageHeadSrc.width = imageStr[2] ?? null;
                    }
                    let newSpl = {
                        title: spl[0],
                        item: spl[1],
                        align: alignStr,
                        aligntd: alignStr2,
                        imageHead: imageHead,
                        imageHeadSrc: imageHeadSrc
                    }
                    this.headerItem.push(newSpl);
                });
            }
            this.dataItem = [];
            this.dataItemInfo.map(ele => {
                let newArray = {...ele, 'selected' : false };
                this.dataItem.push(newArray);
            });
        },
        checkAll(){
            if(this.dataItem.length > 0){
                this.selectedItems = [];
                if(this.firstClick == 0){
                    this.dataItem.map(ele => {
                         ele['selected'] = true;
                    });
                    this.dataItemInfo.map(ele => {
                        this.selectedItems.push(ele);
                    });
                    this.firstClick = 1;
                }else{
                    this.dataItem.map(ele => {
                         ele['selected'] = false;
                    });
                    this.selectedItems = [];
                    this.firstClick = 0;
                }
            }
            this.$emit('change', this.selectedItems);
        },
        chooseItem(event, item){
            let selectedIndex = this.selectedItems.findIndex((ele) => {
                return ele.id == item[this.getIndex];
            });
            if(selectedIndex !== -1){
                this.selectedItems.splice(selectedIndex, 1);
            }else{
                this.selectedItems.push(item);
            }
            this.$emit('change', this.selectedItems);
        },
        clickHeader(item) {
            this.$emit('headClick', item);
        },
        setItemDetail(mainItem, key) {
            let findArray = key.split("|");
            let newKey = findArray[0];
            let item = mainItem[newKey];
            if (findArray[1] !== undefined) {
                item = mainItem[findArray[1]][newKey];
            }
            let itemValue = item ? item : '-';
            return itemValue;
        }
    },
    filters: {
        ucFirst(string) {
            let first = string.charAt(0).toUpperCase();
            return first + string.slice(1);
        }
    },
    watch: {
        $props: {
            handler(val){
                this.loadManage();
            },
            deep: true,
        },
        $attrs: {
            handler(val){
                if(val.selectedValue){
                    this.selectedItems = val.selectedValue;
                    if(this.dataItem.length > 0){
                        this.dataItem.map(ele => {
                            let findSelected = this.selectedItems.find(eleS => eleS[this.getIndex] == ele[this.getIndex]);
                            if(findSelected){
                                ele.selected = true;
                            }else{
                                ele.selected = false;
                            }
                        });
                    }
                }
            },
            deep: true,
        }
    }
}
</script>

<style lang="css" scoped>
    .m-table-content{
        position: relative;
    }

    .m-table-control-left {
        display: inline-flex;
        align-items: center;
        line-height: 1;
        overflow: hidden;
    }
    .m-table-mobile-select{
        margin-right: 5px;
    }
    .onlymobile{
        display: none;
    }
    .m-table table {
        /* border-top: thin solid #ccc; */
        /* border-right: thin solid #ccc; */
        width: 100%;
        border-collapse: collapse;
        transition: all 200ms linear;
    }
    .m-table table tr {
        border-top: thin solid #ccc;
    }
    .m-table table tr td,
    .m-table table thead th {
        /* border-left: thin solid #ccc; */
        padding: 16px 10px;
        font-size: 14px;
        transition: all 200ms linear;
        vertical-align: middle;
        max-width: 150px;
        word-break: break-word;
    }
     .m-table table thead th {
        font-size: 15px;
        /* background: #eee; */
        border-bottom: thin solid #ccc;
        padding: 5px 10px;
        /* text-align: center; */
        transition: all 200ms linear;
    }
    @media (max-width: 571px) {
        .onlymobile {
            display: block;
        }
    }

    .text-align-center {
        text-align: center;
    }

    .text-align-left {
        text-align: left;
    }

    .text-align-right {
        text-align: right;
    }

    .text-align-justify {
        text-align: justify;
    }

    .no-padding {
        padding: 0px!important;
    }

</style>
