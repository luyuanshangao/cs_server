(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-ad3806f0"],{1359:function(t,e,i){"use strict";i.r(e);var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"app-container"},[i("div",{staticClass:"filter-container"},[i("el-input",{staticClass:"filter-item",staticStyle:{width:"200px"},attrs:{placeholder:"角色名",clearable:""},on:{clear:t.handleFilter,blur:t.handleFilter},nativeOn:{keyup:function(e){return!e.type.indexOf("key")&&t._k(e.keyCode,"enter",13,e.key,"Enter")?null:t.handleFilter(e)}},model:{value:t.listQuery.name,callback:function(e){t.$set(t.listQuery,"name",e)},expression:"listQuery.name"}}),t._v(" "),i("el-select",{staticClass:"filter-item",attrs:{placeholder:"状态",clearable:""},on:{change:t.handleFilter},model:{value:t.listQuery.status,callback:function(e){t.$set(t.listQuery,"status",e)},expression:"listQuery.status"}},t._l(t.statusList,(function(t){return i("el-option",{key:t.key,attrs:{value:t.value,label:t.name}})})),1),t._v(" "),i("el-button",{directives:[{name:"waves",rawName:"v-waves"}],staticClass:"filter-item",staticStyle:{"margin-left":"10px"},attrs:{type:"primary",icon:"el-icon-search"},on:{click:t.handleFilter}},[t._v("查询")]),t._v(" "),i("el-button",{directives:[{name:"waves",rawName:"v-waves"}],staticClass:"filter-item",staticStyle:{"margin-left":"5px"},attrs:{type:"info",icon:"el-icon-refresh-left"},on:{click:t.handleClearn}},[t._v("清空")]),t._v(" "),i("el-button",{directives:[{name:"waves",rawName:"v-waves"}],staticClass:"filter-item",staticStyle:{"margin-left":"5px"},attrs:{type:"success",icon:"el-icon-zoom-in"},on:{click:t.handleCreate}},[t._v("新增")]),t._v(" "),i("el-button-group",[i("el-button",{directives:[{name:"waves",rawName:"v-waves"}],staticClass:"filter-item",staticStyle:{"margin-left":"5px"},attrs:{type:"warning",icon:"el-icon-close"},on:{click:t.handleNotUse}},[t._v("无效")]),t._v(" "),i("el-button",{directives:[{name:"waves",rawName:"v-waves"}],staticClass:"filter-item",staticStyle:{"margin-left":"5px"},attrs:{type:"warning",icon:"el-icon-check"},on:{click:t.handleBeUse}},[t._v("有效")])],1),t._v(" "),i("el-table",{directives:[{name:"loading",rawName:"v-loading",value:t.listLoading,expression:"listLoading"}],key:t.tableKey,staticStyle:{width:"100%"},attrs:{data:t.list,border:"",fit:"","highlight-current-row":""},on:{"selection-change":t.checkChange}},[i("el-table-column",{attrs:{type:"selection",width:"55","show-overflow-tooltip":""}}),t._v(" "),i("el-table-column",{attrs:{label:"角色名称",prop:"name",align:"center","min-width":"10%"}}),t._v(" "),i("el-table-column",{attrs:{label:"权限 (点击修改)",align:"center","min-width":"10%"},scopedSlots:t._u([{key:"default",fn:function(e){var n=e.row.roleId;return[i("el-button",{attrs:{type:"text",icon:"el-icon-view",size:"mini"},on:{click:function(e){return t.handleAuthListHandle(n)}}},[t._v("权限修改")])]}}])}),t._v(" "),i("el-table-column",{attrs:{label:"状态",prop:"status",align:"center","min-width":"10%"},scopedSlots:t._u([{key:"default",fn:function(e){var n=e.row.status;return[i("el-tag",{attrs:{type:t._f("statusFilter")(n)}},[t._v("\n            "+t._s(n?"有效":"无效")+"\n          ")])]}}])}),t._v(" "),i("el-table-column",{attrs:{label:"添加时间",prop:"createTime",align:"center","min-width":"10%"},scopedSlots:t._u([{key:"default",fn:function(e){var i=e.row.createTime;return[t._v("\n          "+t._s(t._f("timeFilter")(i))+"\n        ")]}}])}),t._v(" "),i("el-table-column",{attrs:{label:"操作",fixed:"right",align:"center","min-width":"10%"},scopedSlots:t._u([{key:"default",fn:function(e){var n=e.row,a=e.$index;return[i("el-button",{attrs:{type:"primary",size:"mini",icon:"el-icon-delete"},on:{click:function(e){return t.handleEditInfo(n)}}},[t._v("编辑")]),t._v(" "),i("el-button",{attrs:{type:"danger",size:"mini",icon:"el-icon-delete"},on:{click:function(e){return t.handleDelete(n,a)}}},[t._v("删除")])]}}])})],1),t._v(" "),i("pagination",{directives:[{name:"show",rawName:"v-show",value:t.total>0,expression:"total > 0"}],attrs:{total:t.total,page:t.listQuery.page,limit:t.listQuery.size,"page-sizes":[5,10,20]},on:{"update:page":function(e){return t.$set(t.listQuery,"page",e)},"update:limit":function(e){return t.$set(t.listQuery,"size",e)},pagination:t.getList}}),t._v(" "),i("el-dialog",{attrs:{title:t.textMap[t.dialogStatus],visible:t.dialogFormVisible},on:{"update:visible":function(e){t.dialogFormVisible=e}}},[i("el-form",{ref:"dataForm",staticStyle:{width:"400px","margin-left":"50px"},attrs:{rules:t.rules,model:t.temp,"label-position":"right","label-width":"80px"}},[i("el-form-item",{attrs:{label:"角色名称",prop:"name"}},[i("el-input",{model:{value:t.temp.name,callback:function(e){t.$set(t.temp,"name",e)},expression:"temp.name"}})],1),t._v(" "),i("el-form-item",{attrs:{label:"备注",prop:"remark"}},[i("el-input",{model:{value:t.temp.remark,callback:function(e){t.$set(t.temp,"remark",e)},expression:"temp.remark"}})],1),t._v(" "),i("el-form-item",{attrs:{label:"状态",prop:"status"}},[i("el-select",{staticClass:"filter-item",attrs:{placeholder:"状态"},model:{value:t.temp.status,callback:function(e){t.$set(t.temp,"status",e)},expression:"temp.status"}},t._l(t.statusList,(function(t){return i("el-option",{key:t.key,attrs:{value:t.value,label:t.name}})})),1)],1)],1),t._v(" "),i("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[i("el-button",{on:{click:function(e){t.dialogFormVisible=!1}}},[t._v("\n          取消\n        ")]),t._v(" "),i("el-button",{attrs:{type:"primary"},on:{click:function(e){"create"===t.dialogStatus?t.createData():t.updateData()}}},[t._v("\n          确定\n        ")])],1)],1),t._v(" "),i("el-dialog",{attrs:{title:"权限",visible:t.authFormVisible,"close-on-click-modal":!1,width:"50%"},on:{"update:visible":function(e){t.authFormVisible=e}}},[i("el-tree",{ref:"tree",staticStyle:{"max-height":"75vh","overflow-y":"auto"},attrs:{data:t.authList,"default-expand-all":"",props:t.defaultProps,"node-key":"permissionRuleId","show-checkbox":"",disabled:"","default-checked-keys":t.checkedKeys},on:{check:t.changeRuleHandle}}),t._v(" "),i("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[i("el-button",{on:{click:function(e){t.authFormVisible=!1}}},[t._v("取消")]),t._v(" "),i("el-button",{attrs:{type:"primary"},on:{click:t.handleUpdate}},[t._v("确定")])],1)],1)],1)])},a=[],r=(i("5ab2"),i("e10e"),i("e204"),i("6d57"),i("ac9f")),s=i("333d"),l=i("c7a7"),o=i("cc5e"),c=i("ed08");function u(t,e){var i=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),i.push.apply(i,n)}return i}function d(t){for(var e=1;e<arguments.length;e++){var i=null!=arguments[e]?arguments[e]:{};e%2?u(Object(i),!0).forEach((function(e){Object(r["a"])(t,e,i[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(i)):u(Object(i)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(i,e))}))}return t}var p={name:"AccountRole",components:{Pagination:s["a"]},directives:{waves:l["a"]},filters:{statusFilter:function(t){var e={1:"success",0:"info"};return e[t]},timeFilter:function(t){return t?Object(c["d"])(t,"{y}-{m}-{d} {h}:{i}"):"无"}},data:function(){return{rules:{name:[{required:!0,message:"请填写角色名称",trigger:"change"}],status:[{required:!0,message:"请选择状态",trigger:"change"}]},tableKey:0,listLoading:!1,listQuery:{name:"",status:""},statusList:[],list:[],editStatus:{checkArray:[],status:0},page_num:0,total:0,authFormVisible:!1,authList:[],checkedKeys:[],defaultProps:{label:"title"},editRoleId:"",temp:"",dialogFormVisible:!1,dialogStatus:"",textMap:{update:"编辑",create:"新增"}}},mounted:function(){this.getList(),this.getSatusListFilter()},created:function(){this.parseQuery()},methods:{handleFilter:function(){this.getList()},parseQuery:function(){var t={page:1,size:10};this.listQuery=d({},t,{},this.listQuery)},getList:function(){var t=this;this.listLoading=!0,Object(o["j"])(this.listQuery).then((function(e){var i=e.list,n=e.total,a=e.page_num;t.list=i,t.total=n,t.page_num=a,t.page_num=e.page_num,t.listLoading=!1}))},handleNotUse:function(){var t=this;this.editStatus.checkArray.length<=0?this.$notify({title:"警告",message:"未勾选",type:"warning",duration:2e3}):(this.editStatus.status=0,this.list.forEach((function(e){t.editStatus.checkArray.forEach((function(t){e.roleId===t&&e.status&&(e.status=0)}))})),Object(o["g"])(this.editStatus).then((function(t){})))},handleBeUse:function(){var t=this;this.editStatus.checkArray.length<=0?this.$notify({title:"警告",message:"未勾选",type:"warning",duration:2e3}):(this.editStatus.status=1,this.list.forEach((function(e){t.editStatus.checkArray.forEach((function(t){e.roleId===t&&!e.status&&(e.status=1)}))})),Object(o["g"])(this.editStatus).then((function(t){})))},checkChange:function(t){var e=this;this.editStatus.checkArray=[],t.forEach((function(t){e.editStatus.checkArray.push(t.roleId)}))},getSatusListFilter:function(){var t=this;Object(o["h"])().then((function(e){t.statusList=e}))},resetTemp:function(){this.temp={roleId:void 0,name:"",status:"",remark:""}},handleCreate:function(){var t=this;this.resetTemp(),this.dialogStatus="create",this.dialogFormVisible=!0,this.$nextTick((function(){t.$refs["dataForm"].clearValidate()}))},createData:function(){var t=this;this.$refs["dataForm"].validate((function(e){e&&(t.dialogFormVisible=!1,t.$notify({title:"操作结果",message:"成功",type:"success",duration:2e3}),Object(o["d"])(t.temp,t.dialogStatus).then((function(e){t.list.push(e)})))}))},handleEditInfo:function(t){var e=this;this.temp=Object.assign({},t),this.dialogStatus="update",this.dialogFormVisible=!0,this.$nextTick((function(){e.$refs["dataForm"].clearValidate()}))},updateData:function(){var t=this;this.$refs["dataForm"].validate((function(e){e&&(t.dialogFormVisible=!1,t.$notify({title:"操作结果",message:"成功",type:"success",duration:2e3}),Object(o["d"])(t.temp).then((function(e){var i=t.list.findIndex((function(e){return e.roleId===t.temp.roleId}));t.list.splice(i,1,e)})))}))},handleClearn:function(){this.listQuery.name="",this.listQuery.status="",this.handleFilter()},handleAuthListHandle:function(t){var e=this;this.editRoleId=t,Object(o["i"])({roleId:t}).then((function(t){var i=t.auth_list,n=t.checked_keys;e.authList=i,e.checkedKeys=n,e.authFormVisible=!0}))},changeRuleHandle:function(t,e,i){var n=this.$refs.tree.getCheckedNodes(),a=n.map((function(t){return t.permissionRuleId}));this.checkedKeys=a},handleUpdate:function(){this.authFormVisible=!1,this.$notify({title:"操作结果",message:"成功",type:"success",duration:2e3}),Object(o["f"])([this.checkedKeys,this.editRoleId])},handleDelete:function(t,e){var i=this;this.$confirm("此操作将删除数据,是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then((function(){i.$notify({title:"操作结果",message:"成功",type:"success",duration:2e3}),i.list.splice(e,1),Object(o["e"])(t.roleId)}))}}},f=p,m=i("623f"),h=Object(m["a"])(f,n,a,!1,null,null,null);e["default"]=h.exports},"19fc":function(t,e,i){},"1cc6":function(t,e,i){"use strict";var n=i("19fc"),a=i.n(n);a.a},"333d":function(t,e,i){"use strict";var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"pagination-container",class:{hidden:t.hidden}},[i("el-pagination",t._b({attrs:{background:t.background,"current-page":t.currentPage,"page-size":t.pageSize,layout:t.layout,"page-sizes":t.pageSizes,total:t.total},on:{"update:currentPage":function(e){t.currentPage=e},"update:current-page":function(e){t.currentPage=e},"update:pageSize":function(e){t.pageSize=e},"update:page-size":function(e){t.pageSize=e},"size-change":t.handleSizeChange,"current-change":t.handleCurrentChange}},"el-pagination",t.$attrs,!1))],1)},a=[];i("163d");Math.easeInOutQuad=function(t,e,i,n){return t/=n/2,t<1?i/2*t*t+e:(t--,-i/2*(t*(t-2)-1)+e)};var r=function(){return window.requestAnimationFrame||window.webkitRequestAnimationFrame||window.mozRequestAnimationFrame||function(t){window.setTimeout(t,1e3/60)}}();function s(t){document.documentElement.scrollTop=t,document.body.parentNode.scrollTop=t,document.body.scrollTop=t}function l(){return document.documentElement.scrollTop||document.body.parentNode.scrollTop||document.body.scrollTop}function o(t,e,i){var n=l(),a=t-n,o=20,c=0;e="undefined"===typeof e?500:e;var u=function t(){c+=o;var l=Math.easeInOutQuad(c,n,a,e);s(l),c<e?r(t):i&&"function"===typeof i&&i()};u()}var c={name:"Pagination",props:{total:{required:!0,type:Number},page:{type:Number,default:1},limit:{type:Number,default:20},pageSizes:{type:Array,default:function(){return[10,20,30,50]}},layout:{type:String,default:"total, sizes, prev, pager, next, jumper"},background:{type:Boolean,default:!0},autoScroll:{type:Boolean,default:!0},hidden:{type:Boolean,default:!1}},computed:{currentPage:{get:function(){return this.page},set:function(t){this.$emit("update:page",t)}},pageSize:{get:function(){return this.limit},set:function(t){this.$emit("update:limit",t)}}},methods:{handleSizeChange:function(t){this.$emit("pagination",{page:this.currentPage,limit:t}),this.autoScroll&&o(0,800)},handleCurrentChange:function(t){this.$emit("pagination",{page:t,limit:this.pageSize}),this.autoScroll&&o(0,800)}}},u=c,d=(i("1cc6"),i("623f")),p=Object(d["a"])(u,n,a,!1,null,"f3b72548",null);e["a"]=p.exports},"8d41":function(t,e,i){},c7a7:function(t,e,i){"use strict";i("8d41");var n="@@wavesContext";function a(t,e){function i(i){var n=Object.assign({},e.value),a=Object.assign({ele:t,type:"hit",color:"rgba(0, 0, 0, 0.15)"},n),r=a.ele;if(r){r.style.position="relative",r.style.overflow="hidden";var s=r.getBoundingClientRect(),l=r.querySelector(".waves-ripple");switch(l?l.className="waves-ripple":(l=document.createElement("span"),l.className="waves-ripple",l.style.height=l.style.width=Math.max(s.width,s.height)+"px",r.appendChild(l)),a.type){case"center":l.style.top=s.height/2-l.offsetHeight/2+"px",l.style.left=s.width/2-l.offsetWidth/2+"px";break;default:l.style.top=(i.pageY-s.top-l.offsetHeight/2-document.documentElement.scrollTop||document.body.scrollTop)+"px",l.style.left=(i.pageX-s.left-l.offsetWidth/2-document.documentElement.scrollLeft||document.body.scrollLeft)+"px"}return l.style.backgroundColor=a.color,l.className="waves-ripple z-active",!1}}return t[n]?t[n].removeHandle=i:t[n]={removeHandle:i},i}e["a"]={bind:function(t,e){t.addEventListener("click",a(t,e),!1)},update:function(t,e){t.removeEventListener("click",t[n].removeHandle,!1),t.addEventListener("click",a(t,e),!1)},unbind:function(t){t.removeEventListener("click",t[n].removeHandle,!1),t[n]=null,delete t[n]}}},cc5e:function(t,e,i){"use strict";i.d(e,"h",(function(){return a})),i.d(e,"j",(function(){return r})),i.d(e,"i",(function(){return s})),i.d(e,"f",(function(){return l})),i.d(e,"g",(function(){return o})),i.d(e,"d",(function(){return c})),i.d(e,"a",(function(){return u})),i.d(e,"e",(function(){return d})),i.d(e,"c",(function(){return p})),i.d(e,"b",(function(){return f}));var n=i("b775");function a(){return Object(n["a"])({url:"v1/admin/role/getStatus",method:"post"})}function r(t){return Object(n["a"])({url:"v1/admin/role/list",method:"get",params:t})}function s(t){return Object(n["a"])({url:"v1/admin/role/authList",method:"get",params:t})}function l(t){return Object(n["a"])({url:"v1/admin/role/editAuthPermission",method:"post",data:t})}function o(t){return Object(n["a"])({url:"v1/admin/role/editStatus",method:"post",data:t})}function c(t,e){var i="create"===e?"v1/admin/role/addAuthRole":"v1/admin/role/editAuthRole";return Object(n["a"])({url:i,method:"post",data:t})}function u(){return Object(n["a"])({url:"v1/admin/role/allPermissionRule",method:"get"})}function d(t){return Object(n["a"])({url:"v1/admin/role/del",method:"get",params:{id:t}})}function p(t,e){var i="edit"!==e?"v1/admin/role/addPermissionRule":"v1/admin/role/editPermissionRule";return Object(n["a"])({url:i,method:"post",data:t})}function f(t){return Object(n["a"])({url:"v1/admin/role/delPermissionRule",method:"get",params:{permissionRuleId:t}})}}}]);