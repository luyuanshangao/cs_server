(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-04fd4368"],{"1a49":function(t,e,a){"use strict";a.r(e);var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("cate-detail",{attrs:{"is-edit":""}})},i=[],o=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"detail"},[a("el-form",{ref:"postForm",staticClass:"form-container",attrs:{model:t.postForm}},[a("sticky",{attrs:{"z-index":10,"class-name":"sub-navbar"}},[a("el-button",{staticStyle:{"margin-left":"10px"},attrs:{type:"info"},on:{click:t.toHisory}},[t._v("\n        返回\n      ")]),t._v(" "),a("el-button",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticStyle:{"margin-left":"10px"},attrs:{type:"success"},on:{click:t.submitForm}},[t._v("\n        "+t._s(t.isEdit?"确定编辑":"确定新增")+"\n      ")])],1),t._v(" "),a("div",{staticClass:"detail-container"},[a("el-row",[a("Warning"),t._v(" "),a("el-col",{attrs:{span:24}},[a("el-form-item",{staticStyle:{"margin-bottom":"0"},attrs:{prop:"catImg"}},[a("Upload",{attrs:{action:t.action,"file-list":t.fileList},on:{onSuccess:t.onUploadSuccess,onRemove:t.onUploadRemove},model:{value:t.postForm.catImg,callback:function(e){t.$set(t.postForm,"catImg",e)},expression:"postForm.catImg"}})],1)],1),t._v(" "),a("el-col",{attrs:{span:24}},[a("div",{staticClass:"postInfo-container"},[a("el-row",t._l(t.pathData,(function(e){return a("el-col",{key:e.value,staticClass:"form-item-author",attrs:{span:24}},[a("el-form-item",{attrs:{"label-width":t.labelWidth,label:e.name}},[a("el-input",{staticStyle:{width:"100%"},attrs:{placeholder:"无",disabled:""},model:{value:e.catName,callback:function(a){t.$set(e,"catName",a)},expression:"cat.catName"}})],1)],1)})),1)],1)])],1)],1)],1)],1)},r=[],l=a("b804"),s=function(){var t=this,e=t.$createElement;t._self._c;return t._m(0)},u=[function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("aside",[a("span",{staticStyle:{color:"#20b6f9"}},[t._v("分类图片：")]),t._v("\n  点击“添加图片”进行上传，只能上传一张图片。上传成功后，如需重新进行上传请先清除之前上传的文件,分类图片为选填\n  "),a("hr")])}],c=a("623f"),d={},m=Object(c["a"])(d,s,u,!1,null,null,null),p=m.exports,h=a("b8bc"),f=a("c405"),v=a("709b"),g=(a("1aba"),{catImg:""}),b={name:"Detail",components:{Sticky:l["a"],Warning:p,Upload:h["a"]},filters:{sizeType:function(t){if(0===t)return"0 B";var e=1024,a=["B","KB","MB","GB","TB"],n=Math.floor(Math.log(t)/Math.log(e));return(t/Math.pow(e,n)).toPrecision(3)+" "+a[n]}},props:{isEdit:{type:Boolean,default:!1}},data:function(){return{action:"".concat("http://www.gzwilly.work","/v1/admin/category/upload"),tempRoute:{},loading:!1,postForm:Object.assign({},g),fileList:[],pathData:[],labelWidth:"120px"}},computed:{},watch:{$route:function(t,e){this.$route.query.id&&this.isEdit&&(this.reset(),this.getCategoryData(this.$route.query.id))}},created:function(){if(this.$route.query.id&&this.isEdit){var t=this.$route.query.id;this.getCategoryData(t)}this.tempRoute=Object.assign({},this.$route)},mounted:function(){},methods:{showGuide:function(){console.log("showGuide")},toHisory:function(){window.history.length>1?this.$router.go(-1):this.$router.push("/")},getCategoryData:function(t){var e=this;Object(f["c"])(t).then((function(t){e.setData(t)}))},submitForm:function(){var t=this;this.$refs.postForm.validate((function(e){if(console.log(e),!e)return!1;t.loading=!0;var a=Object.assign({},t.postForm);t.isEdit?Object(f["f"])(a).then((function(e){t.loading=!1,t.$notify({title:"操作结果",message:"成功",type:"success",duration:2e3})})).catch((function(){t.loading=!1})):Object(f["a"])(a).then((function(e){t.loading=!1,t.$notify({title:"操作结果",message:"成功",type:"success",duration:2e3})})).catch((function(){t.loading=!1}))}))},setData:function(t){var e=t.catId,a=t.catImg,n=t.pathData;console.log(a),this.pathData=n,this.postForm={catId:e,catImg:a||""},this.fileList=a?[{name:a,url:"".concat("http://www.gzwilly.work")+a}]:[]},onUploadSuccess:function(t){var e=t.catImg;this.postForm.catImg=e},onUploadRemove:function(t){var e=this;Object(v["c"])(this.postForm.catImg).then((function(t){e.$notify({title:"操作结果",message:"成功",type:"success",duration:2e3})})),this.postForm.catImg=""},reset:function(){this.fileList=[],this.pathData=[]}}},y=b,w=(a("aea3"),Object(c["a"])(y,o,r,!1,null,"79ae3689",null)),_=w.exports,x={name:"CategoryEdit",components:{cateDetail:_},props:{},data:function(){return{}},computed:{},watch:{},created:function(){},mounted:function(){},methods:{}},$=x,C=Object(c["a"])($,n,i,!1,null,"024e2243",null);e["default"]=C.exports},"1aba":function(t,e,a){"use strict";var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"material-input__component",class:t.computedClasses},[a("div",{class:{iconClass:t.icon}},[t.icon?a("i",{staticClass:"el-input__icon material-input__icon",class:["el-icon-"+t.icon]}):t._e(),t._v(" "),"email"===t.type?a("input",{directives:[{name:"model",rawName:"v-model",value:t.currentValue,expression:"currentValue"}],staticClass:"material-input",attrs:{name:t.name,placeholder:t.fillPlaceHolder,readonly:t.readonly,disabled:t.disabled,autocomplete:t.autoComplete,required:t.required,type:"email"},domProps:{value:t.currentValue},on:{focus:t.handleMdFocus,blur:t.handleMdBlur,input:[function(e){e.target.composing||(t.currentValue=e.target.value)},t.handleModelInput]}}):t._e(),t._v(" "),"url"===t.type?a("input",{directives:[{name:"model",rawName:"v-model",value:t.currentValue,expression:"currentValue"}],staticClass:"material-input",attrs:{name:t.name,placeholder:t.fillPlaceHolder,readonly:t.readonly,disabled:t.disabled,autocomplete:t.autoComplete,required:t.required,type:"url"},domProps:{value:t.currentValue},on:{focus:t.handleMdFocus,blur:t.handleMdBlur,input:[function(e){e.target.composing||(t.currentValue=e.target.value)},t.handleModelInput]}}):t._e(),t._v(" "),"number"===t.type?a("input",{directives:[{name:"model",rawName:"v-model",value:t.currentValue,expression:"currentValue"}],staticClass:"material-input",attrs:{name:t.name,placeholder:t.fillPlaceHolder,step:t.step,readonly:t.readonly,disabled:t.disabled,autocomplete:t.autoComplete,max:t.max,min:t.min,minlength:t.minlength,maxlength:t.maxlength,required:t.required,type:"number"},domProps:{value:t.currentValue},on:{focus:t.handleMdFocus,blur:t.handleMdBlur,input:[function(e){e.target.composing||(t.currentValue=e.target.value)},t.handleModelInput]}}):t._e(),t._v(" "),"password"===t.type?a("input",{directives:[{name:"model",rawName:"v-model",value:t.currentValue,expression:"currentValue"}],staticClass:"material-input",attrs:{name:t.name,placeholder:t.fillPlaceHolder,readonly:t.readonly,disabled:t.disabled,autocomplete:t.autoComplete,max:t.max,min:t.min,required:t.required,type:"password"},domProps:{value:t.currentValue},on:{focus:t.handleMdFocus,blur:t.handleMdBlur,input:[function(e){e.target.composing||(t.currentValue=e.target.value)},t.handleModelInput]}}):t._e(),t._v(" "),"tel"===t.type?a("input",{directives:[{name:"model",rawName:"v-model",value:t.currentValue,expression:"currentValue"}],staticClass:"material-input",attrs:{name:t.name,placeholder:t.fillPlaceHolder,readonly:t.readonly,disabled:t.disabled,autocomplete:t.autoComplete,required:t.required,type:"tel"},domProps:{value:t.currentValue},on:{focus:t.handleMdFocus,blur:t.handleMdBlur,input:[function(e){e.target.composing||(t.currentValue=e.target.value)},t.handleModelInput]}}):t._e(),t._v(" "),"text"===t.type?a("input",{directives:[{name:"model",rawName:"v-model",value:t.currentValue,expression:"currentValue"}],staticClass:"material-input",attrs:{name:t.name,placeholder:t.fillPlaceHolder,readonly:t.readonly,disabled:t.disabled,autocomplete:t.autoComplete,minlength:t.minlength,maxlength:t.maxlength,required:t.required,type:"text"},domProps:{value:t.currentValue},on:{focus:t.handleMdFocus,blur:t.handleMdBlur,input:[function(e){e.target.composing||(t.currentValue=e.target.value)},t.handleModelInput]}}):t._e(),t._v(" "),a("span",{staticClass:"material-input-bar"}),t._v(" "),a("label",{staticClass:"material-label"},[t._t("default")],2)])])},i=[],o=(a("163d"),{name:"MdInput",props:{icon:String,name:String,type:{type:String,default:"text"},value:[String,Number],placeholder:String,readonly:Boolean,disabled:Boolean,min:String,max:String,step:String,minlength:Number,maxlength:Number,required:{type:Boolean,default:!0},autoComplete:{type:String,default:"off"},validateEvent:{type:Boolean,default:!0}},data:function(){return{currentValue:this.value,focus:!1,fillPlaceHolder:null}},computed:{computedClasses:function(){return{"material--active":this.focus,"material--disabled":this.disabled,"material--raised":Boolean(this.focus||this.currentValue)}}},watch:{value:function(t){this.currentValue=t}},methods:{handleModelInput:function(t){var e=t.target.value;this.$emit("input",e),"ElFormItem"===this.$parent.$options.componentName&&this.validateEvent&&this.$parent.$emit("el.form.change",[e]),this.$emit("change",e)},handleMdFocus:function(t){this.focus=!0,this.$emit("focus",t),this.placeholder&&""!==this.placeholder&&(this.fillPlaceHolder=this.placeholder)},handleMdBlur:function(t){this.focus=!1,this.$emit("blur",t),this.fillPlaceHolder=null,"ElFormItem"===this.$parent.$options.componentName&&this.validateEvent&&this.$parent.$emit("el.form.blur",[this.currentValue])}}}),r=o,l=(a("ad53"),a("623f")),s=Object(l["a"])(r,n,i,!1,null,"d9b004ee",null);e["a"]=s.exports},2323:function(t,e,a){"use strict";var n=a("f262"),i=a.n(n);i.a},"709b":function(t,e,a){"use strict";a.d(e,"f",(function(){return i})),a.d(e,"g",(function(){return o})),a.d(e,"a",(function(){return r})),a.d(e,"h",(function(){return l})),a.d(e,"e",(function(){return s})),a.d(e,"b",(function(){return u})),a.d(e,"c",(function(){return c})),a.d(e,"d",(function(){return d}));var n=a("b775");function i(){return Object(n["a"])({url:"v1/admin/banner/getStatus",method:"post"})}function o(t){return Object(n["a"])({url:"v1/admin/banner/list",method:"get",params:t})}function r(t){return Object(n["a"])({url:"v1/admin/banner/create",method:"post",data:t})}function l(t){return Object(n["a"])({url:"v1/admin/banner/edit",method:"post",data:t})}function s(t){return Object(n["a"])({url:"v1/admin/banner/get",method:"get",params:{id:t}})}function u(t){return Object(n["a"])({url:"v1/admin/banner/del",method:"get",params:{id:t}})}function c(t){return Object(n["a"])({url:"v1/admin/banner/deleteImageFile",method:"get",params:{name:t}})}function d(t){return Object(n["a"])({url:"v1/admin/banner/editStatus",method:"post",data:t})}},"77b5":function(t,e,a){},ad53:function(t,e,a){"use strict";var n=a("f9dd"),i=a.n(n);i.a},aea3:function(t,e,a){"use strict";var n=a("77b5"),i=a.n(n);i.a},b804:function(t,e,a){"use strict";var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{style:{height:t.height+"px",zIndex:t.zIndex}},[a("div",{class:t.className,style:{top:t.isSticky?t.stickyTop+"px":"",zIndex:t.zIndex,position:t.position,width:t.width,height:t.height+"px"}},[t._t("default",[a("div",[t._v("sticky")])])],2)])},i=[],o=(a("163d"),{name:"Sticky",props:{stickyTop:{type:Number,default:0},zIndex:{type:Number,default:1},className:{type:String,default:""}},data:function(){return{active:!1,position:"",width:void 0,height:void 0,isSticky:!1}},mounted:function(){this.height=this.$el.getBoundingClientRect().height,window.addEventListener("scroll",this.handleScroll),window.addEventListener("resize",this.handleResize)},activated:function(){this.handleScroll()},destroyed:function(){window.removeEventListener("scroll",this.handleScroll),window.removeEventListener("resize",this.handleResize)},methods:{sticky:function(){this.active||(this.position="fixed",this.active=!0,this.width=this.width+"px",this.isSticky=!0)},handleReset:function(){this.active&&this.reset()},reset:function(){this.position="",this.width="auto",this.active=!1,this.isSticky=!1},handleScroll:function(){var t=this.$el.getBoundingClientRect().width;this.width=t||"auto";var e=this.$el.getBoundingClientRect().top;e<this.stickyTop?this.sticky():this.handleReset()},handleResize:function(){this.isSticky&&(this.width=this.$el.getBoundingClientRect().width+"px")}}}),r=o,l=a("623f"),s=Object(l["a"])(r,n,i,!1,null,null,null);e["a"]=s.exports},b8bc:function(t,e,a){"use strict";var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"singleImageUpload2 upload-container"},[a("el-upload",{staticClass:"image-uploader",attrs:{action:t.action,headers:t.headers,multiple:!1,limit:1,"before-upload":t.beforeUpload,"on-success":t.onSuccess,"on-error":t.onError,"on-remove":t.onRemove,"file-list":t.fileList,"on-exceed":t.onExceed,disabled:t.disabled,"list-type":t.listType,drag:"","show-file-list":"",accept:".jpg,.jpeg,.png,.JPG,.JPEG,.GIF"}},[a("i",{staticClass:"el-icon-upload"}),t._v(" "),0===t.fileList.length?a("div",{staticClass:"el-upload__text"},[t._v("\n      请将图片拖入或\n      "),a("em",[t._v("点击上传")])]):a("div",{staticClass:"el-upload__text"},[t._v("\n      图片已上传\n    ")])])],1)},i=[],o=a("5f87"),r={name:"ImageUpload",props:{fileList:{type:Array,default:function(){return[]}},action:{type:String,default:""},disabled:{type:Boolean,default:!1},listType:{type:String,default:"picture"}},data:function(){return{}},computed:{headers:function(){return{token:"".concat(Object(o["a"])())}}},methods:{onRemove:function(t){this.$emit("onRemove",t)},onExceed:function(){this.$notify({type:"warning",message:"只能上传一张图片"})},beforeUpload:function(t){this.$emit("beforeUpload",t)},onSuccess:function(t,e){var a=t.code,n=t.msg,i=t.data;1===a?this.$emit("onSuccess",i):(this.$notify({type:"error",message:n&&"上传失败，失败原因：".concat(n)||"上传失败"}),this.$emit("onError",i))},onError:function(t){var e=t.message&&JSON.parse(t.message)||"上传失败";this.$notify({type:"error",message:e.msg&&"上传失败，失败原因：".concat(e.msg)||"上传失败"}),this.$emit("onError",t)}}},l=r,s=(a("2323"),a("623f")),u=Object(s["a"])(l,n,i,!1,null,"d2833f6a",null);e["a"]=u.exports},c405:function(t,e,a){"use strict";a.d(e,"e",(function(){return i})),a.d(e,"a",(function(){return o})),a.d(e,"f",(function(){return r})),a.d(e,"c",(function(){return l})),a.d(e,"d",(function(){return s})),a.d(e,"b",(function(){return u}));var n=a("b775");function i(t){return Object(n["a"])({url:"v1/admin/category/list",method:"get",params:t})}function o(t){return Object(n["a"])({url:"v1/admin/category/create",method:"post",data:t})}function r(t){return Object(n["a"])({url:"v1/admin/category/edit",method:"post",data:t})}function l(t){return Object(n["a"])({url:"v1/admin/category/get",method:"get",params:{catId:t}})}function s(){return Object(n["a"])({url:"v1/admin/category/getStatus",method:"post"})}function u(t){return Object(n["a"])({url:"v1/admin/category/del",method:"get",params:{catId:t}})}},f262:function(t,e,a){},f9dd:function(t,e,a){}}]);