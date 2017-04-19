/* jce - 2.6.9 | 2017-03-09 | http://www.joomlacontenteditor.net | Copyright (C) 2006 - 2017 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
!function(tinymce){var DOM=tinymce.DOM,Event=tinymce.dom.Event,each=(tinymce.is,tinymce.each),VK=tinymce.VK;tinyMCE.onAddEditor.add(function(mgr,ed){tinymce.isMac&&tinymce.isGecko&&!tinymce.isIE11&&ed.onKeyDown.add(function(ed,e){!VK.metaKeyPressed(e)||e.shiftKey||37!=e.keyCode&&39!=e.keyCode||(ed.selection.getSel().modify("move",37==e.keyCode?"backward":"forward","word"),e.preventDefault())})}),tinymce.util.PreviewCss=function(ed,fmt){function removeVars(val){return val.replace(/%(\w+)/g,"")}var name,previewElm,parentFontSize,dom=ed.dom,previewCss="",previewStyles=ed.settings.preview_styles;return previewStyles===!1?"":(previewStyles||(previewStyles="font-family font-size font-weight text-decoration text-transform color background-color"),name=fmt.block||fmt.inline||"span",previewElm=dom.create(name),each(fmt.styles,function(value,name){value=removeVars(value),value&&dom.setStyle(previewElm,name,value)}),each(fmt.attributes,function(value,name){value=removeVars(value),value&&dom.setAttrib(previewElm,name,value)}),each(fmt.classes,function(value){value=removeVars(value),dom.hasClass(previewElm,value)||dom.addClass(previewElm,value)}),dom.setStyles(previewElm,{position:"absolute",left:-65535}),ed.getBody().appendChild(previewElm),parentFontSize=dom.getStyle(ed.getBody(),"fontSize",!0),parentFontSize=/px$/.test(parentFontSize)?parseInt(parentFontSize,10):0,each(previewStyles.split(" "),function(name){var value=dom.getStyle(previewElm,name,!0);if("background-color"!=name||!/transparent|rgba\s*\([^)]+,\s*0\)/.test(value)||(value=dom.getStyle(ed.getBody(),name,!0),"#ffffff"!=dom.toHex(value).toLowerCase())){if("font-size"==name&&/em|%$/.test(value)){if(0===parentFontSize)return;value=parseFloat(value,10)/(/%$/.test(value)?100:1),value=value*parentFontSize+"px"}previewCss+=name+":"+value+";"}}),dom.remove(previewElm),previewCss)},tinymce.create("tinymce.ui.ButtonDialog:tinymce.ui.Button",{ButtonDialog:function(id,s,ed){this.parent(id,s,ed),this.settings=s=tinymce.extend({content:"",buttons:[]},this.settings),this.editor=ed,this.onRenderDialog=new tinymce.util.Dispatcher(this),this.onShowDialog=new tinymce.util.Dispatcher(this),this.onHideDialog=new tinymce.util.Dispatcher(this),s.dialog_container=s.dialog_container||DOM.doc.body},showDialog:function(){var p2,t=this,ed=this.editor,s=this.settings,e=DOM.get(t.id);if(!t.isDisabled()){if(this.storeSelection(),t.isDialogRendered||t.renderDialog(),t.isDialogVisible)return t.hideDialog();if(DOM.show(t.id+"_dialog"),s.url){var iframe=DOM.get(t.id+"_iframe");iframe.src=s.url}p2=DOM.getPos(e),DOM.setStyles(t.id+"_dialog",{left:p2.x,top:p2.y+e.clientHeight+5,zIndex:2e5}),e=0,this.isActive()?DOM.addClass(t.id+"_dialog",this.classPrefix+"DialogActive"):DOM.removeClass(t.id+"_dialog",this.classPrefix+"DialogActive"),Event.add(ed.getDoc(),"mousedown",t.hideDialog,t),Event.add(DOM.doc,"mousedown",function(e){for(var n=e.target;n&&n!=DOM.getRoot()&&n.nodeType&&9!==n.nodeType;){if(n==DOM.get(t.id+"_dialog"))return;n=n.parentNode}t.hideDialog()}),t.onShowDialog.dispatch(t),t._focused&&(t._keyHandler=Event.add(t.id+"_dialog","keydown",function(e){27==e.keyCode&&t.hideDialog()})),t.isDialogVisible=1}},storeSelection:function(){tinymce.isIE&&(this.editor.focus(),this.bookmark=this.editor.selection.getBookmark(1))},restoreSelection:function(){this.bookmark&&(this.editor.selection.moveToBookmark(this.bookmark),this.editor.focus()),this.bookmark=0},renderDialog:function(){var m,w,v,t=this,s=this.settings,ed=this.editor;return s.class+=" defaultSkin","default"!==ed.getParam("skin")&&(s.class+=" "+ed.getParam("skin")+"Skin"),(v=ed.getParam("skin_variant"))&&(s.class+=" "+ed.getParam("skin")+"Skin"+v.substring(0,1).toUpperCase()+v.substring(1)),s.class+="rtl"==ed.settings.directionality?" mceRtl":"",w=DOM.add(s.dialog_container,"div",{role:"presentation",id:t.id+"_dialog",class:s.class,style:"position:absolute;left:0;top:-1000px;"}),w=DOM.add(w,"div",{class:this.classPrefix+"Dialog"}),m=DOM.add(w,"div",{class:this.classPrefix+"DialogContent"}),s.width&&DOM.setStyle(w,"width",s.width),tinymce.is(s.content,"string")?DOM.setHTML(m,s.content):DOM.add(m,s.content),s.url&&DOM.add(m,"iframe",{id:t.id+"_iframe",src:s.url,style:{border:0,width:"100%",height:"100%"},onload:function(){t.isDialogRendered=!0,t.onRenderDialog.dispatch(t)}}),m=DOM.add(w,"div",{class:this.classPrefix+"DialogButtons"}),each(s.buttons,function(o){var btn=DOM.add(m,"button",{type:"button",class:"mceDialogButton",id:t.id+"_button_"+o.id},o.title||"");o.click&&Event.add(btn,"click",function(e){e.preventDefault(),t.restoreSelection();var s=o.click.call(o.scope||t,e);s&&t.hideDialog()})}),s.url||(t.isDialogRendered=!0,t.onRenderDialog.dispatch(t)),w},setButtonDisabled:function(button,state){var id=this.id+"_button_"+button;state?DOM.addClass(id,"disabled"):DOM.removeClass(id,"disabled")},setButtonLabel:function(button,label){DOM.setHTML(this.id+"_button_"+button,label)},hideDialog:function(e){var t=this;e&&"mousedown"==e.type&&DOM.getParent(e.target,function(e){return e.id===t.id||e.id===t.id+"_open"})||(e&&DOM.getParent(e.target,".mceDialog")||(t.setState("Selected",0),Event.remove(DOM.doc,"mousedown",t.hideDialog,t),DOM.hide(t.id+"_dialog")),t.isDialogVisible=0,t.onHideDialog.dispatch(t))},postRender:function(){var t=this,s=t.settings;this.editor;Event.add(t.id,"click",function(){t.isDisabled()||(s.onclick&&s.onclick(t.value),t.showDialog())})},destroy:function(){this.parent(),Event.clear(this.id+"_dialog"),DOM.remove(this.id+"_dialog")}})}(tinymce);