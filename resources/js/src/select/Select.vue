<template>
    <div class="vue-select" ref="vue-select" :set-id="setKey" :key="setKey" :theme="themeData" @mouseleave="closeOptions" @mouseenter="openOptions(false)" :style="`${minWidth ? 'min-width:'+ minWidth + 'px' : ''}`">
        <div class="vue-select-label-text" v-html="label" v-if="label"></div>
        <div class="vue-select-label" :class="{'active' : activeClick}" @click="openOptions" :style="`${noBorder != undefined ? 'border: unset!important' : ''}`">
            <div class="select-label">
                <template v-if="multiple && multipleSelect != null && multipleSelect.length">
                    <div class="multiple-option-item">
                        <span v-for="mItem, is in multipleSelect" :key="is" @click="removeSelectedItem(mItem, is)">
                            <template v-if="returnObject">
                                {{ mItem[itemText] }} <strong>&times;</strong>
                            </template>
                            <template v-else>
                                {{ mItem }} <strong>&times;</strong>
                            </template>
                        </span>
                    </div>
                </template>
                <template v-else>{{ currentValue }}</template>
            </div>
            <div class="select-icon">
                <img :src="require('./asset/down-chevron.png').default" class="img-fluid">
                <!-- <span>&#x276E;</span> -->
            </div>
        </div>
        <transition name="fade-xt" appear v-if="activeClick">
            <div class="vue-select-search">
                <input ref="filterData" type="text" placeholder="filter..." v-model="interSearch"  @input="updateSearch($event.target.value)" class="inter-search">
                <small v-if="items.length">* Total <strong>({{ items.length }})</strong> items in the list </small>
                <small v-else>* There are <strong>no</strong> item in the list </small>
                <div class="vue-select-options">
                    <template v-if="returnObject == true">
                        <div class="vue-option" :class="{ 'active' : checkActiveLink(item) }" @click="chooseItem(item)" v-for="item, ik in items" :key="ik">
                            <div class="option-item-box">
                                <div class="options-checkbox">
                                    <img :src="require('./asset/checkbox.png').default" class="img-fluid">
                                    <!-- <span>&#x2713;</span> -->
                                </div>
                                <slot :name="item[itemValue]" :item="item">
                                    <span v-html="item[itemText]"></span>
                                </slot>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="vue-option" :class="{ 'active' : checkActiveLink(item) }" @click="chooseItem(item)" v-for="item, ik in items" :key="ik">
                            <div class="option-item-box">
                                <div class="options-checkbox">
                                    <img :src="require('./asset/checkbox.png').default" class="img-fluid">
                                    <!-- <span>&#x2713;</span> -->
                                </div>
                                <slot :name="item" :item="{item}">
                                    <span v-html="item"></span>
                                </slot>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>

export default {
    props: ['options', 'object', 'min-width', 'item-text', 'theme', 'no-border', 'label', 'hover', 'return-array', 'item-value', 'multi-select', 'auto-hide'],
    data: () => ({
        items: [],
        returnObject: false,
        currentValue: "Choose an item",
        multipleSelect: [],
        activeClick: false,
        themeData: "blue",
        setKey: null,
        multiple: false,
        interSearch: null,
        backupItem: [],
        tabIndex: 0,
        imageStr: {
            checked: null,
            arrow: null
        }
    }),
    model: {
        prop: 'modelData',
        event: 'change'
    },
    created() {
        if (this.multiple !== undefined) {
            this.currentValue = "Choose options";
        }
        this.setKey = Math.floor(Math.random(10000) * 1000);
        this.interSearch = "";
        this.items = this.options;
        this.imageStr.checked = "./asset/checkbox.png";
        this.imageStr.arrow = "./asset/down-chevron.png";
        this.initCurrentData(this.$props, this.$attrs);
        this.setTheme();
    },
    mounted() {
        document.addEventListener('click', (e) => {
            let target = e.target;
            let targetClosest = target.closest(".vue-select");
            if (targetClosest == undefined) {
                this.closeBox(true);
            } else {
                let clickSetId = $(targetClosest).attr('set-id');
                let getRefs = $(this.$refs['vue-select']).attr('set-id');
                if (clickSetId != getRefs) {
                    this.closeBox(true);
                }
            }
        });
    },
    methods : {
        initCurrentData(props, attr) {
            if (props.object !== undefined) {
                this.returnObject = true;
            }
            if (props.itemValue === undefined) {
                this.itemValue = this.itemText;
            }
            if (props.multiSelect !== undefined) {
                this.multiple = true;
            }
            let findItem = null;
            if (this.returnObject == true) {
                let modelDataFilter = attr.modelData;
                if(typeof attr.modelData == 'object') {
                    modelDataFilter = attr.modelData ? attr.modelData[this.itemValue] : [];
                }
                findItem = this.items.find( (ele) => ele[this.itemValue] == modelDataFilter );
            } else {
                findItem = this.items.find( (ele) => ele == attr.modelData );
            }
            if (findItem || this.multiple) {
                this.initalCurrentValue();
            }
        },
        setTheme(theme = null) {
            if (theme == null) theme = this.$props.theme;
            if (theme !== undefined || theme != null) {
                if (['dark', 'default', 'blue', 'red', 'cyan'].find(ele => ele == this.$props.theme)) {
                    this.themeData = this.$props.theme;
                }
            }
        },
        initalCurrentValue() {
            let modelData = this.$attrs.modelData;
            if (this.returnObject) {
                if (this.multiple) {
                    if (typeof modelData == 'array' || typeof modelData == 'object') {
                        this.multipleSelect = modelData;
                    }
                } else {
                    if (typeof modelData == 'object') {
                        this.currentValue = modelData[this.itemText];
                    } else {
                        let findItem = this.items.find( (ele) => ele[this.itemValue] == modelData );
                        this.currentValue = findItem[this.itemText];
                    }
                }
            } else {
                if (this.multiple) {
                    this.multipleSelect = modelData;
                } else {
                    this.currentValue = modelData;
                }
            }
        },
        changeSelected() {
            let value = this.currentValue;
            this.$attrs.modelValue = value;
            this.emitValue('change', value);
        },
        emitValue(event, value) {
            this.$emit(event, value);
        },
        closeOptions() {
            if (this.$props.hover != undefined) {
                this.close();
            }
        },
        openOptions(type = true) {
            if (type == false && this.$props.hover == undefined) {
                return false;
            }
            this.initList();
            if (this.activeClick == false) {
                this.activeClick = true;
                this.$nextTick(() => this.$refs.filterData.focus())
            } else {
                if (type == false && this.$props.hover != undefined) {
                    return false;
                } else {
                    this.closeBox();
                }
            }
        },
        updateSearch(value) {
            if (value == null || value == "") {
                this.items = this.options;
            }
            let newArray = [];
            let search = value.toUpperCase();
            if (typeof search == 'number') {
                search = search.toString();
            }
            if(this.returnObject == true){
                newArray = this.items.filter(e => {
                    let searchValue = e[this.itemText].toUpperCase();
                    return searchValue.includes(search);
                });
            }else{
                newArray = this.items.filter(e => {
                    let searchValue = e.toUpperCase();
                    return searchValue.includes(search);
                });
            }
            if (newArray.length) {
                this.items = newArray;
            } else {
                this.items = this.options;
            }
        },
        chooseItem(item){
            this.closeBoxOptions();
            if (this.multiple == true) {
                this.addMultiple(item);
            } else {
                if (this.returnObject == true) {
                    this.currentValue = item[this.itemText];
                    if (this.$props.returnArray !== undefined) {
                        this.emitValue('change', item);
                    } else {
                        this.emitValue('change', item[this.itemValue]);
                    }
                } else {
                    this.currentValue = item;
                    this.emitValue('change', item);
                }
            }
            this.initList();
        },
        addMultiple(item) {
            let checkOld = null;

            if (this.multipleSelect != null) {
                if (this.returnObject) {
                    checkOld = this.multipleSelect.findIndex((ele) => ele[this.itemValue] == item[this.itemValue]);
                } else {
                    checkOld = this.multipleSelect.findIndex((ele) => ele == item);
                }
                if (checkOld != -1) {
                    this.multipleSelect.splice(checkOld, 1);
                } else {
                    this.multipleSelect.push(item);
                }
            } else {
                this.multipleSelect = [];
                this.multipleSelect.push(item);
            }

            this.returnEmit();
        },
        initList() {
            this.interSearch = "";
            this.items = this.options;
        },
        close() {
            this.closeBox(true);
        },
        open() {
            this.openOptions();
        },
        closeBox(defVal = false) {
            this.closeBoxOptions(defVal);
            this.initList();
        },
        closeBoxOptions(defVal = false) {
            if (defVal == false) {
                if (this.multiple == false) {
                    if (this.$props.autoHide !== undefined && this.$props.autoHide == false) {
                        this.activeClick = true;
                    } else {
                        this.activeClick = false;
                    }
                }
            } else {
                this.activeClick = false;
            }
        },
        checkActiveLink(item) {
            let value = this.multipleSelect;
            let returnValue = false;
            if (!this.multiple) {
                if (this.returnObject) {
                    returnValue = item[this.itemText] == this.currentValue;
                } else {
                    returnValue = item == this.currentValue;
                }
            } else {
                if(this.returnObject){
                    let itemValue = item[this.itemText];
                    if(value){
                        let index = value.findIndex(ele => itemValue == ele[this.itemText]);
                        if(index != -1){
                            returnValue = true;
                        }else{
                            returnValue = false;
                        }
                    }else{
                        returnValue = false;
                    }
                }else{
                    if(value){
                        let index = value.findIndex(ele => item == ele);
                        if(index != -1){
                            returnValue = true;
                        }else{
                            returnValue = false;
                        }
                    }else{
                        returnValue = false;
                    }
                }
            }
            return returnValue;
        },
        removeSelectedItem(item, index) {
            this.multipleSelect.splice(index, 1);
            this.returnEmit();
        },
        returnEmit() {
            let returnMultiple = [];
            if (this.$props.returnArray !== undefined) {
                this.multipleSelect.map(ele => {
                    if (this.returnObject) {
                        returnMultiple.push(ele[this.itemValue]);
                    } else {
                        returnMultiple.push(ele);
                    }
                });
                this.emitValue('change', returnMultiple.length ? returnMultiple : null);
            } else {
                this.emitValue('change', (this.multipleSelect && this.multipleSelect.length) ? this.multipleSelect : null);
            }
        }
    },
    watch: {
        $attrs : {
            handler(val) {
                this.initCurrentData(this.$props, val);
            },
            deep: true
        },
        $props : {
            handler(val) {
                this.initCurrentData(val, this.$attrs);
            },
            deep: true
        }
    }
}
</script>

<style scoped lang="scss">
.vue-select{
    position: relative;
}

.vue-select-label {
    border: thin solid #7e829b;
    border-radius: 0.25rem;
    padding: 7px 10px;
    background: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    flex-direction: row;
    overflow-x: auto;
    white-space: nowrap;
    scrollbar-width: thin;
    scrollbar-color: #4f5f6f;
}
.vue-select-label:active {
    // background: #ebebeb;
}
.vue-select-label::-webkit-scrollbar {
    height: 5px;
}
.vue-select-label::-webkit-scrollbar-thumb {
    background: #4f5f6f;
}
.vue-select-label .select-icon {
    transition: all 200ms linear;
    height: 24px;
    width: 24px;
    line-height: 24px;
}
.vue-select-label.active .select-icon {
    transform: rotate(180deg);
    transition: all 200ms linear;
}
.vue-select-label .select-icon img{
    height: 20px;
    margin-top: -3px;
}

.vue-select-search {
    position: absolute;
    width: 100%;
    background: #fff;
    box-shadow: 0 5px 15px 0px rgb(0 0 0 / 15%);
    padding: 10px;
    border-bottom-right-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
    transition: all 200ms linear;
    max-height: 350px;
    overflow: auto;
    scrollbar-width: thin;
    scrollbar-color: #6f7f8f;
    z-index: 99999;
}
.vue-select-search::-webkit-scrollbar {
    width: 5px;
}
.vue-select-search::-webkit-scrollbar-thumb {
    background: #6f7f8f;
}
.vue-select-search input {
    padding: 5px 10px;
    border-radius: 0.25rem;
    width: 100%;
    background: #eee;
    outline: 0;
    display: inline-block;
    border: unset;
}
.vue-select-options .vue-option{
    cursor: pointer;
    padding: 8px;
    border-radius: 0.25rem;
    transition: all 200ms linear;
}
.vue-select-options .vue-option:hover, .vue-select-options .vue-option.active {
    background: #80e9a724;
}
.fade-xt-enter-active, .fade-xt-leave-active {
    transition: 200ms;
    opacity: 1;
}
.fade-xt-enter, .fade-xt-leave-to {
    transition: 200ms;
    opacity: 0;
}
.option-item-box {
    display: flex;
    align-items: center;
    justify-content: flex-start;
}
.option-item-box img {
    height: 12px;
    margin-top: -3px;
    filter: invert(1);
}
.options-checkbox {
    height: 20px;
    width: 20px;
    background: #ddd;
    text-align: center;
    line-height: 20px;
    border-radius: 20px;
    margin-right: 10px;
}
.vue-select-options .vue-option.active .options-checkbox {
    background: #80e9ba;
}
.multiple-option-item span {
    background: #80e9ba;
    margin-right: 5px;
    padding: 3px 5px;
    border-radius: 0.25rem;
}

.multiple-option-item span:active {
    background: #f96f5d;
    color: #fff;
}

.vue-select-options .vue-option.active .options-checkbox span {
    color: #ddd;
}

.vue-select[theme="dark"] .vue-select-options .vue-option.active .options-checkbox {
    background: #8f8f8f;
}
.vue-select[theme="dark"] .multiple-option-item span {
    background: #dfdfdf;
}
.vue-select[theme="blue"] .vue-select-options .vue-option.active .options-checkbox {
    background: #0d6efd;
}
.vue-select[theme="blue"] .multiple-option-item span {
    background: #0d6efd;
    color: #fff;
}
.vue-select[theme="red"] .vue-select-options .vue-option.active .options-checkbox {
    background: #f14242;
}
.vue-select[theme="red"] .multiple-option-item span {
    background: #f14242;
    color: #fff;
}
.vue-select[theme="cyan"] .vue-select-options .vue-option.active .options-checkbox {
    background: #0dfde9;
}
.vue-select[theme="cyan"] .multiple-option-item span {
    background: #0dfde9;
    color: #2f3f4f;
}
.vue-select[theme="blue"] .vue-select-options .vue-option:hover, .vue-select[theme="blue"] .vue-select-options .vue-option.active {
    background: #3889ff14;
}
.vue-select[theme="dark"] .vue-select-options .vue-option:hover, .vue-select[theme="dark"] .vue-select-options .vue-option.active {
    background: #f5f5f5;
}

.vue-select[theme="red"] .vue-select-options .vue-option:hover, .vue-select[theme="red"] .vue-select-options .vue-option.active {
    background: #ff969626;
}
.vue-select[theme="cyan"] .vue-select-options .vue-option:hover, .vue-select[theme="cyan"] .vue-select-options .vue-option.active {
    background: #0dfde924;
}
.vue-select.required {
    border: thin solid #ff7c7c;
}

.vue-select.required .select-icon img{
    filter: invert(16%) sepia(99%) saturate(7404%) hue-rotate(4deg) brightness(95%) contrast(118%);
}

.vue-select.required .vue-select-search {
    scrollbar-color: #ff7c7c;
}
.vue-select.required .vue-select-search::-webkit-scrollbar-thumb {
    background: #ff7c7c;
}
.vue-select.required .vue-select-label {
    border-color: #ff7c7c;
}
.vue-select .vue-select-label-text {
    font-size: 14px;
    display: inline-block;
    margin-bottom: 5px;
}
.vue-select-search small {
    display: inline-block;
    font-size: 12px;
    margin-top: 10px;
    letter-spacing: 0.7px;
    margin-bottom: 10px;
}
.vue-select .select-icon span {
    transform: rotate(-90deg);
    display: inline-block;
}
.vue-select {
    text-align: left;
}
</style>
