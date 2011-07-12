﻿/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

(function(){CKEDITOR.dialog.add('link',function(b){var c=CKEDITOR.plugins.link,d=function(){var G=this.getDialog(),H=G.getContentElement('target','popupFeatures'),I=G.getContentElement('target','linkTargetName'),J=this.getValue();if(!H||!I)return;H=H.getElement();H.hide();I.setValue('');switch(J){case 'frame':I.setLabel(b.lang.link.targetFrameName);I.getElement().show();break;case 'popup':H.show();I.setLabel(b.lang.link.targetPopupName);I.getElement().show();break;default:I.setValue(J);I.getElement().hide();break;}},e=function(){var G=this.getDialog(),H=['urlOptions','anchorOptions','emailOptions'],I=this.getValue(),J=G.definition.getContents('upload'),K=J&&J.hidden;if(I=='url'){if(b.config.linkShowTargetTab)G.showPage('target');if(!K)G.showPage('upload');}else{G.hidePage('target');if(!K)G.hidePage('upload');}for(var L=0;L<H.length;L++){var M=G.getContentElement('info',H[L]);if(!M)continue;M=M.getElement().getParent().getParent();if(H[L]==I+'Options')M.show();else M.hide();}G.layout();},f=/^javascript:/,g=/^mailto:([^?]+)(?:\?(.+))?$/,h=/subject=([^;?:@&=$,\/]*)/,i=/body=([^;?:@&=$,\/]*)/,j=/^#(.*)$/,k=/^((?:http|https|ftp|news):\/\/)?(.*)$/,l=/^(_(?:self|top|parent|blank))$/,m=/^javascript:void\(location\.href='mailto:'\+String\.fromCharCode\(([^)]+)\)(?:\+'(.*)')?\)$/,n=/^javascript:([^(]+)\(([^)]+)\)$/,o=/\s*window.open\(\s*this\.href\s*,\s*(?:'([^']*)'|null)\s*,\s*'([^']*)'\s*\)\s*;\s*return\s*false;*\s*/,p=/(?:^|,)([^=]+)=(\d+|yes|no)/gi,q=function(G,H){var I=H&&(H.data('cke-saved-href')||H.getAttribute('href'))||'',J,K,L,M,N={};if(J=I.match(f)){if(z=='encode')I=I.replace(m,function(ah,ai,aj){return 'mailto:'+String.fromCharCode.apply(String,ai.split(','))+(aj&&x(aj));});else if(z)I.replace(n,function(ah,ai,aj){if(ai==A.name){N.type='email';var ak=N.email={},al=/[^,\s]+/g,am=/(^')|('$)/g,an=aj.match(al),ao=an.length,ap,aq;for(var ar=0;ar<ao;ar++){aq=decodeURIComponent(x(an[ar].replace(am,'')));ap=A.params[ar].toLowerCase();ak[ap]=aq;}ak.address=[ak.name,ak.domain].join('@');}});}if(!N.type){if(L=I.match(j)){N.type='anchor';N.anchor={};N.anchor.name=N.anchor.id=L[1];}else if(K=I.match(g)){var O=I.match(h),P=I.match(i);N.type='email';var Q=N.email={};Q.address=K[1];O&&(Q.subject=decodeURIComponent(O[1]));P&&(Q.body=decodeURIComponent(P[1]));}else{var R='';for(var S in G.config.link_types){var T=G.config.link_types[S];for(var U in T){var V=T[U];if(V.url==I){R=a(S);break;}}}if(R){N.type=R;N.customLinkUrl=I;}else if(I&&(M=I.match(k))){N.type='url';N.url={};
N.url.protocol=M[1];N.url.url=M[2];}else N.type='url';}}if(H){var W=H.getAttribute('target');N.target={};N.adv={};if(!W){var X=H.data('cke-pa-onclick')||H.getAttribute('onclick'),Y=X&&X.match(o);if(Y){N.target.type='popup';N.target.name=Y[1];var Z;while(Z=p.exec(Y[2])){if((Z[2]=='yes'||Z[2]=='1')&&!(Z[1] in {height:1,width:1,top:1,left:1}))N.target[Z[1]]=true;else if(isFinite(Z[2]))N.target[Z[1]]=Z[2];}}}else{var aa=W.match(l);if(aa)N.target.type=N.target.name=W;else{N.target.type='frame';N.target.name=W;}}var ab=this,ac=function(ah,ai){var aj=H.getAttribute(ai);if(aj!==null)N.adv[ah]=aj||'';};ac('advId','id');ac('advLangDir','dir');ac('advAccessKey','accessKey');N.adv.advName=H.data('cke-saved-name')||H.getAttribute('name')||'';ac('advLangCode','lang');ac('advTabIndex','tabindex');ac('advTitle','title');ac('advContentType','type');CKEDITOR.plugins.link.synAnchorSelector?N.adv.advCSSClasses=D(H):ac('advCSSClasses','class');ac('advCharset','charset');ac('advStyles','style');ac('advRel','rel');}var ad=new CKEDITOR.dom.nodeList(G.document.$.anchors),ae=N.anchors=[],af;for(var U=0,ag=ad.count();U<ag;U++){af=ad.getItem(U);ae[U]={name:af.getAttribute('name'),id:af.getAttribute('id')};}this._.selectedElement=H;return N;},r=function(G,H){if(H[G])this.setValue(H[G][this.id]||'');},s=function(G){return r.call(this,'target',G);},t=function(G){return r.call(this,'adv',G);},u=function(G,H){if(!H[G])H[G]={};H[G][this.id]=this.getValue()||'';},v=function(G){return u.call(this,'target',G);},w=function(G){return u.call(this,'adv',G);};function x(G){return G.replace(/\\'/g,"'");};function y(G){return G.replace(/'/g,'\\$&');};var z=b.config.emailProtection||'';if(z&&z!='encode'){var A={};z.replace(/^([^(]+)\(([^)]+)\)$/,function(G,H,I){A.name=H;A.params=[];I.replace(/[^,\s]+/g,function(J){A.params.push(J);});});}function B(G){var H,I=A.name,J=A.params,K,L;H=[I,'('];for(var M=0;M<J.length;M++){K=J[M].toLowerCase();L=G[K];M>0&&H.push(',');H.push("'",L?y(encodeURIComponent(G[K])):'',"'");}H.push(')');return H.join('');};function C(G){var H,I=G.length,J=[];for(var K=0;K<I;K++){H=G.charCodeAt(K);J.push(H);}return 'String.fromCharCode('+J.join(',')+')';};function D(G){var H=G.getAttribute('class');return H?H.replace(/\s*(?:cke_anchor_empty|cke_anchor)(?:\s*$)?/g,''):'';};var E=b.lang.common,F=b.lang.link;return{title:F.title,minWidth:350,minHeight:230,contents:[{id:'info',label:F.info,title:F.info,elements:[{id:'linkType',type:'select',label:F.type,'default':'url',items:[[F.toUrl,'url'],[F.toAnchor,'anchor'],[F.toEmail,'email']],onChange:e,setup:function(G){if(G.type)this.setValue(G.type);
},commit:function(G){G.type=this.getValue();}},{type:'vbox',id:'urlOptions',children:[{type:'hbox',widths:['25%','75%'],children:[{id:'protocol',type:'select',label:E.protocol,'default':'http://',items:[['http://','http://'],['https://','https://'],['ftp://','ftp://'],['news://','news://'],[F.other,'']],setup:function(G){if(G.url)this.setValue(G.url.protocol||'');},commit:function(G){if(!G.url)G.url={};G.url.protocol=this.getValue();}},{type:'text',id:'url',label:E.url,required:true,onLoad:function(){this.allowOnChange=true;},onKeyUp:function(){var G=this;G.allowOnChange=false;var H=G.getDialog().getContentElement('info','protocol'),I=G.getValue(),J=/^(http|https|ftp|news):\/\/(?=.)/i,K=/^((javascript:)|[#\/\.\?])/i,L=J.exec(I);if(L){G.setValue(I.substr(L[0].length));H.setValue(L[0].toLowerCase());}else if(K.test(I))H.setValue('');G.allowOnChange=true;},onChange:function(){if(this.allowOnChange)this.onKeyUp();},validate:function(){var G=this.getDialog();if(G.getContentElement('info','linkType')&&G.getValueOf('info','linkType')!='url')return true;if(this.getDialog().fakeObj)return true;var H=CKEDITOR.dialog.validate.notEmpty(F.noUrl);return H.apply(this);},setup:function(G){this.allowOnChange=false;if(G.url)this.setValue(G.url.url);this.allowOnChange=true;},commit:function(G){this.onChange();if(!G.url)G.url={};G.url.url=this.getValue();this.allowOnChange=false;}}],setup:function(G){if(!this.getDialog().getContentElement('info','linkType'))this.getElement().show();}},{type:'button',id:'browse',hidden:'true',filebrowser:'info:url',label:E.browseServer}]},{type:'vbox',id:'anchorOptions',width:260,align:'center',padding:0,children:[{type:'fieldset',id:'selectAnchorText',label:F.selectAnchor,setup:function(G){if(G.anchors.length>0)this.getElement().show();else this.getElement().hide();},children:[{type:'hbox',id:'selectAnchor',children:[{type:'select',id:'anchorName','default':'',label:F.anchorName,style:'width: 100%;',items:[['']],setup:function(G){var H=this;H.clear();H.add('');for(var I=0;I<G.anchors.length;I++){if(G.anchors[I].name)H.add(G.anchors[I].name);}if(G.anchor)H.setValue(G.anchor.name);var J=H.getDialog().getContentElement('info','linkType');if(J&&J.getValue()=='email')H.focus();},commit:function(G){if(!G.anchor)G.anchor={};G.anchor.name=this.getValue();}},{type:'select',id:'anchorId','default':'',label:F.anchorId,style:'width: 100%;',items:[['']],setup:function(G){var H=this;H.clear();H.add('');for(var I=0;I<G.anchors.length;I++){if(G.anchors[I].id)H.add(G.anchors[I].id);
}if(G.anchor)H.setValue(G.anchor.id);},commit:function(G){if(!G.anchor)G.anchor={};G.anchor.id=this.getValue();}}],setup:function(G){if(G.anchors.length>0)this.getElement().show();else this.getElement().hide();}}]},{type:'html',id:'noAnchors',style:'text-align: center;',html:'<div role="label" tabIndex="-1">'+CKEDITOR.tools.htmlEncode(F.noAnchors)+'</div>',focus:true,setup:function(G){if(G.anchors.length<1)this.getElement().show();else this.getElement().hide();}}],setup:function(G){if(!this.getDialog().getContentElement('info','linkType'))this.getElement().hide();}},{type:'vbox',id:'emailOptions',padding:1,children:[{type:'text',id:'emailAddress',label:F.emailAddress,required:true,validate:function(){var G=this.getDialog();if(!G.getContentElement('info','linkType')||G.getValueOf('info','linkType')!='email')return true;var H=CKEDITOR.dialog.validate.notEmpty(F.noEmail);return H.apply(this);},setup:function(G){if(G.email)this.setValue(G.email.address);var H=this.getDialog().getContentElement('info','linkType');if(H&&H.getValue()=='email')this.select();},commit:function(G){if(!G.email)G.email={};G.email.address=this.getValue();}},{type:'text',id:'emailSubject',label:F.emailSubject,setup:function(G){if(G.email)this.setValue(G.email.subject);},commit:function(G){if(!G.email)G.email={};G.email.subject=this.getValue();}},{type:'textarea',id:'emailBody',label:F.emailBody,rows:3,'default':'',setup:function(G){if(G.email)this.setValue(G.email.body);},commit:function(G){if(!G.email)G.email={};G.email.body=this.getValue();}}],setup:function(G){if(!this.getDialog().getContentElement('info','linkType'))this.getElement().hide();}}]},{id:'target',label:F.target,title:F.target,elements:[{type:'hbox',widths:['50%','50%'],children:[{type:'select',id:'linkTargetType',label:E.target,'default':'notSet',style:'width : 100%;',items:[[E.notSet,'notSet'],[F.targetFrame,'frame'],[F.targetPopup,'popup'],[E.targetNew,'_blank'],[E.targetTop,'_top'],[E.targetSelf,'_self'],[E.targetParent,'_parent']],onChange:d,setup:function(G){if(G.target)this.setValue(G.target.type||'notSet');d.call(this);},commit:function(G){if(!G.target)G.target={};G.target.type=this.getValue();}},{type:'text',id:'linkTargetName',label:F.targetFrameName,'default':'',setup:function(G){if(G.target)this.setValue(G.target.name);},commit:function(G){if(!G.target)G.target={};G.target.name=this.getValue().replace(/\W/gi,'');}}]},{type:'vbox',width:'100%',align:'center',padding:2,id:'popupFeatures',children:[{type:'fieldset',label:F.popupFeatures,children:[{type:'hbox',children:[{type:'checkbox',id:'resizable',label:F.popupResizable,setup:s,commit:v},{type:'checkbox',id:'status',label:F.popupStatusBar,setup:s,commit:v}]},{type:'hbox',children:[{type:'checkbox',id:'location',label:F.popupLocationBar,setup:s,commit:v},{type:'checkbox',id:'toolbar',label:F.popupToolbar,setup:s,commit:v}]},{type:'hbox',children:[{type:'checkbox',id:'menubar',label:F.popupMenuBar,setup:s,commit:v},{type:'checkbox',id:'fullscreen',label:F.popupFullScreen,setup:s,commit:v}]},{type:'hbox',children:[{type:'checkbox',id:'scrollbars',label:F.popupScrollBars,setup:s,commit:v},{type:'checkbox',id:'dependent',label:F.popupDependent,setup:s,commit:v}]},{type:'hbox',children:[{type:'text',widths:['50%','50%'],labelLayout:'horizontal',label:E.width,id:'width',setup:s,commit:v},{type:'text',labelLayout:'horizontal',widths:['50%','50%'],label:F.popupLeft,id:'left',setup:s,commit:v}]},{type:'hbox',children:[{type:'text',labelLayout:'horizontal',widths:['50%','50%'],label:E.height,id:'height',setup:s,commit:v},{type:'text',labelLayout:'horizontal',label:F.popupTop,widths:['50%','50%'],id:'top',setup:s,commit:v}]}]}]}]},{id:'upload',label:F.upload,title:F.upload,hidden:true,filebrowser:'uploadButton',elements:[{type:'file',id:'upload',label:E.upload,style:'height:40px',size:29},{type:'fileButton',id:'uploadButton',label:E.uploadSubmit,filebrowser:'info:url','for':['upload','upload']}]},{id:'advanced',label:F.advanced,title:F.advanced,elements:[{type:'vbox',padding:1,children:[{type:'hbox',widths:['45%','35%','20%'],children:[{type:'text',id:'advId',label:F.id,setup:t,commit:w},{type:'select',id:'advLangDir',label:F.langDir,'default':'',style:'width:110px',items:[[E.notSet,''],[F.langDirLTR,'ltr'],[F.langDirRTL,'rtl']],setup:t,commit:w},{type:'text',id:'advAccessKey',width:'80px',label:F.acccessKey,maxLength:1,setup:t,commit:w}]},{type:'hbox',widths:['45%','35%','20%'],children:[{type:'text',label:F.name,id:'advName',setup:t,commit:w},{type:'text',label:F.langCode,id:'advLangCode',width:'110px','default':'',setup:t,commit:w},{type:'text',label:F.tabIndex,id:'advTabIndex',width:'80px',maxLength:5,setup:t,commit:w}]}]},{type:'vbox',padding:1,children:[{type:'hbox',widths:['45%','55%'],children:[{type:'text',label:F.advisoryTitle,'default':'',id:'advTitle',setup:t,commit:w},{type:'text',label:F.advisoryContentType,'default':'',id:'advContentType',setup:t,commit:w}]},{type:'hbox',widths:['45%','55%'],children:[{type:'text',label:F.cssClasses,'default':'',id:'advCSSClasses',setup:t,commit:w},{type:'text',label:F.charset,'default':'',id:'advCharset',setup:t,commit:w}]},{type:'hbox',widths:['45%','55%'],children:[{type:'text',label:F.rel,'default':'',id:'advRel',setup:t,commit:w},{type:'text',label:F.styles,'default':'',id:'advStyles',setup:t,commit:w}]}]}]}],onShow:function(){var G=this.getParentEditor(),H=G.getSelection(),I=null;
if((I=c.getSelectedLink(G))&&I.hasAttribute('href'))H.selectElement(I);else I=null;this.setupContent(q.apply(this,[G,I]));},onOk:function(){var G={},H=[],I={},J=this,K=this.getParentEditor();this.commitContent(I);switch(I.type||'url'){case 'url':var L=I.url&&I.url.protocol!=undefined?I.url.protocol:'http://',M=I.url&&I.url.url||'';G['data-cke-saved-href']=M.indexOf('/')===0?M:L+M;break;case 'anchor':var N=I.anchor&&I.anchor.name,O=I.anchor&&I.anchor.id;G['data-cke-saved-href']='#'+(N||O||'');break;case 'email':var P,Q=I.email,R=Q.address;switch(z){case '':case 'encode':var S=encodeURIComponent(Q.subject||''),T=encodeURIComponent(Q.body||''),U=[];S&&U.push('subject='+S);T&&U.push('body='+T);U=U.length?'?'+U.join('&'):'';if(z=='encode'){P=["javascript:void(location.href='mailto:'+",C(R)];U&&P.push("+'",y(U),"'");P.push(')');}else P=['mailto:',R,U];break;default:var V=R.split('@',2);Q.name=V[0];Q.domain=V[1];P=['javascript:',B(Q)];}G['data-cke-saved-href']=P.join('');break;default:var M=I.customLinkUrl||'';G['data-cke-saved-href']=M;break;}if(I.target){if(I.target.type=='popup'){var W=["window.open(this.href, '",I.target.name||'',"', '"],X=['resizable','status','location','toolbar','menubar','fullscreen','scrollbars','dependent'],Y=X.length,Z=function(aj){if(I.target[aj])X.push(aj+'='+I.target[aj]);};for(var aa=0;aa<Y;aa++)X[aa]=X[aa]+(I.target[X[aa]]?'=yes':'=no');Z('width');Z('left');Z('height');Z('top');W.push(X.join(','),"'); return false;");G['data-cke-pa-onclick']=W.join('');H.push('target');}else{if(I.target.type!='notSet'&&I.target.name)G.target=I.target.name;else H.push('target');H.push('data-cke-pa-onclick','onclick');}}if(I.adv){var ab=function(aj,ak){var al=I.adv[aj];if(al)G[ak]=al;else H.push(ak);};ab('advId','id');ab('advLangDir','dir');ab('advAccessKey','accessKey');if(I.adv.advName)G.name=G['data-cke-saved-name']=I.adv.advName;else H=H.concat(['data-cke-saved-name','name']);ab('advLangCode','lang');ab('advTabIndex','tabindex');ab('advTitle','title');ab('advContentType','type');ab('advCSSClasses','class');ab('advCharset','charset');ab('advStyles','style');ab('advRel','rel');}G.href=G['data-cke-saved-href'];if(!this._.selectedElement){var ac=K.getSelection(),ad=ac.getRanges(true);if(ad.length==1&&ad[0].collapsed){var ae=new CKEDITOR.dom.text(I.type=='email'?I.email.address:G['data-cke-saved-href'],K.document);ad[0].insertNode(ae);ad[0].selectNodeContents(ae);ac.selectRanges(ad);}var af=new CKEDITOR.style({element:'a',attributes:G});af.type=2;af.apply(K.document);
}else{var ag=this._.selectedElement,ah=ag.data('cke-saved-href'),ai=ag.getHtml();ag.setAttributes(G);ag.removeAttributes(H);if(I.adv&&I.adv.advName&&CKEDITOR.plugins.link.synAnchorSelector)ag.addClass(ag.getChildCount()?'cke_anchor':'cke_anchor_empty');if(ah==ai||I.type=='email'&&ai.indexOf('@')!=-1)ag.setHtml(I.type=='email'?I.email.address:G['data-cke-saved-href']);delete this._.selectedElement;}},onLoad:function(){if(!b.config.linkShowAdvancedTab)this.hidePage('advanced');if(!b.config.linkShowTargetTab)this.hidePage('target');},onFocus:function(){var G=this.getContentElement('info','linkType'),H;if(G&&G.getValue()=='url'){H=this.getContentElement('info','url');H.select();}}};});var a=function(b){b=b.replace(' ','_').toLowerCase();b=b.replace(/\'/g,'');b=b.replace(/\"/g,'');return b;};})();
