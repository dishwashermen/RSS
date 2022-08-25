document.addEventListener('DOMContentLoaded', function() {
	
	CANSEND = false;

	if ($_GET('Hash') || $_GET('A') || $_GET('P')) {
		
		ImportJS('md5, Dialog, Valid, Worker');
		
		QN = document.querySelector('#QName');
		
		QH = document.querySelector('#QHeader');
		
		QB = document.querySelector('#QBody');
		
		BC = document.querySelector('#QButton');
		
		let FD = new FormData();
					        
		$_GET('Hash') && FD.append('Hash', $_GET('Hash'));
		
		$_GET('A') && FD.append('A', $_GET('A'));
		
		$_GET('P') && FD.append('P', $_GET('P'));
		
		if ($_GET('P')) SendRequest('Public/Php/GetPersonal.php', FD);
		
		else SendRequest('Public/Php/GetProject.php', FD);
		
	}
        
});

ImportJS = function(js) {
	
	[].forEach.call(js.split(','), function(uri) {
	
		let script = document.createElement('script');
	
		script.src  = 'Public/Js/' + uri.trim() + '.js';
	
		document.head.appendChild(script);
		
	});
	
}

function SendRequest(location, param) {
	
	let R = false;
	
	if (window.XMLHttpRequest) {
		
		R = new XMLHttpRequest();

	} else if (window.ActiveXObject) {

		try {
			
			R = new ActiveXObject('Msxml2.XMLHTTP');
			
		} catch (e) {
		
			try {
				
				R = new ActiveXObject('Microsoft.XMLHTTP');
				
			} catch (e) {}
			
		}
	
	}
	
	if (R) {
		
		R.onreadystatechange = function() {
			
			if (R.readyState == XMLHttpRequest.DONE) { 
			    
			    CANSEND = true;
			
				if (R.status == 200 && R.responseText.length) {
					
					RESP = JSON.parse(R.responseText);

					switch (RESP.Action) {
						
						case 'Resume':
						
							QDATA = RESP.Q;
							
							RULESDATA = RESP.RuleData;

							RESP.HistoryState && (HIO = JSON.parse(RESP.HistoryState));
							
							UID = RESP.Uid;
							
							STATEINDEX = RESP.StateIndex.toString();
							
							RESP.JournalIndex && (JOURNALINDEX = RESP.JournalIndex);
							
							PageManager.Page('MainContainer', RESP.Reload);
							
							if (RULES.VISUAL && RULES.VISUAL.includes(STATEINDEX)) VisualRuleProcessing();
							
							document.querySelector('#Submit') && document.querySelector('#Submit').classList.remove('submit-preloader');
							
							document.querySelector('#Back') && document.querySelector('#Back').classList.remove('submit-preloader');
						
							if (PROJECT.Total == STATEINDEX) document.querySelector('.top-progress').classList.add('hidden');

							if (RESP.LimitText != null && RESP.LimitText != '') al('ModalDialog', RESP.LimitText);
							
						break;		

						case 'Personal':
						
							PROJECT = RESP.Project;

							if (PROJECT.Difference) {
								
								let PRD_A = PROJECT.Difference.split(',');
								
								PROJECT.Difference = [];
								
								[].forEach.call(PRD_A, function(x) {
									
									let XR = x.match(/^([^\[]+).*$/);
									
									PROJECT.Difference.push(XR[1]);

								});

							}
							
							RULES = RESP.Rules;
						
							HISTORY = PROJECT.History == 'ON' ? true : false;
							
							QND = PROJECT.QNHidden == 'ON' ? false : true;
							
							JOURNALMODE = PROJECT.Version.match(/Journal\[(.*)\]/);

							if (JOURNALMODE != null) {
								
								JOURNALMODE = JOURNALMODE[1].split(',');
								
								JF = JOURNALMODE[0];
								
								JL = JOURNALMODE[1];
								
								JC = JOURNALMODE[2];
								
							}
							
							QDATA = RESP.Q;
							
							RULESDATA = RESP.RuleData;

							RESP.HistoryState && (HIO = JSON.parse(RESP.HistoryState));
							
							UID = RESP.Uid;
							
							STATEINDEX = RESP.StateIndex.toString();
							
							RESP.JournalIndex && (JOURNALINDEX = RESP.JournalIndex);
							
							PageManager.Page('MainContainer', RESP.Reload);
							
							if (RULES.VISUAL && RULES.VISUAL.includes(STATEINDEX)) VisualRuleProcessing();
							
							document.querySelector('#Submit') && document.querySelector('#Submit').classList.remove('submit-preloader');
							
							document.querySelector('#Back') && document.querySelector('#Back').classList.remove('submit-preloader');
						
							if (PROJECT.Total == STATEINDEX) document.querySelector('.top-progress').classList.add('hidden');
						
						break;
		                
		                case 'Denied': 
						
							al('LoginError');
		                    
		                break;		                
						
						case 'Welcome':
						
							PROJECT = RESP.Project;

							if (PROJECT.Difference) {
								
								let PRD_A = PROJECT.Difference.split(',');
								
								PROJECT.Difference = [];
								
								[].forEach.call(PRD_A, function(x) {
									
									let XR = x.match(/^([^\[]+).*$/);
									
									PROJECT.Difference.push(XR[1]);

								});

							}								
							
							FILEDATA = RESP.FileData;
							
							RULES = RESP.Rules;
							
							LIMIT = RESP.Limit;
						
							HISTORY = PROJECT.History == 'ON' ? true : false;
							
							QND = PROJECT.QNHidden == 'ON' ? false : true;
							
							REGTYPE = PROJECT.AuthType.match(/(^file|^user|^random)\[?(\d+)?([^\]]+)?\]?(protect$)?/);
							
							if (REGTYPE[1] == 'file') {
								
								document.querySelector('#NotInListChecker').classList.remove('hidden');
								
								document.querySelector('#NotInListChecker input').addEventListener('change', listener)
								
							}
							
							JOURNALMODE = PROJECT.Version.match(/Journal\[(.*)\]/);

							if (JOURNALMODE != null) {
								
								JOURNALMODE = JOURNALMODE[1].split(',');
								
								JF = JOURNALMODE[0];
								
								JL = JOURNALMODE[1];
								
								JC = JOURNALMODE[2];
								
							}
							
							PageManager.Page('HomePage');
							
						break;
						
				    }
		
				} 
			
			}
			
		}
		
		R.open('POST', location);

		R.send(param);
		
	} else console.log("NOT AJAX");
	
}

PageManager = {
	
	HomePage: function() {
		
		document.querySelector('.project-name-container').textContent = PROJECT.Name;
		
		if (PROJECT.Description) document.querySelector('.project-description-container').innerHTML = PROJECT.Description;
		
		else document.querySelector('.project-description-container').classList.add('hidden');
		
		let AIC = document.querySelector('#AuthInputContainer tr');
		
		let AICNIL = document.querySelector('#AuthInputContainerNotInList tr');
		
		let AITD;
		
		let AITDNIL;			
		
		if (REGTYPE[1] == 'file' && REGTYPE[2] != null) for (let GC = 0; GC < REGTYPE[2]; GC ++) {
			
			AITD = new elem('td', {});
			
			AITDNIL = new elem('td', {});	
			
			let INP = new elem('input', {type: 'text', id: 'AuthIL' + (GC + 1), classname: 'auth-input', attr: 'list=AuthList' + (GC + 1), placeholder: FILEDATA[GC].fieldName, addevent: (REGTYPE[2] > 1 ? 'blur' : '')});
			
			let INPNIL = new elem('input', {type: 'text', id: 'AuthNIL' + (GC + 1), classname: 'auth-input', placeholder: FILEDATA[GC].fieldName});																					 
			let LIST = new elem('datalist', {id: 'AuthList' + (GC + 1)});
						
			[].forEach.call(FILEDATA[GC].fieldData.flat(), function(y) {
				
				let OPT = new elem('option', {textcontent: y});
				
				LIST.append(OPT);
				
			});
			
			if (LIST.childNodes.length > 0) AITD.append(INP, LIST);
						
			else AITD.append(INP);
			
			AITDNIL.append(INPNIL);	
			
			AIC.append(AITD);
			
			AICNIL.append(AITDNIL);	
			
		} else if (REGTYPE[1] == 'user') {
			
			AITD = new elem('td', {});
			
			if (REGTYPE[3] == 'PHONE') AITD.append(new elem('input', {type: 'tel', id: 'AuthIL1', classname: 'auth-input', addevent: 'input', pattern: '\\+7\\s?[\\(]{0,1}9[0-9]{2}[\\)]{0,1}\\s?\\d{3}[-]{0,1}\\d{2}[-]{0,1}\\d{2}', placeholder: '+7(___)___-__-__', value: '+7(___)___-__-__'}));
			
			else AITD.append(new elem('input', {type: 'text', id: 'AuthIL1', classname: 'auth-input', placeholder: REGTYPE[3]}));
			
			AIC.append(AITD);
			
		} else if (REGTYPE[1] == 'random') {
			
			AITD = new elem('td', {});
			
			AITD.append(new elem('input', {type: 'text', id: 'AuthIL1', classname: 'auth-input', placeholder: 'Случайная строка', value: md5(new Date())}));
			
			AIC.append(AITD);
			
		}
		
		if (REGTYPE[4] == 'protect') {
			
			AITD = new elem('td', {});
			
			AITDNIL = new elem('td', {});
			
			AITD.append(new elem('input', {type: 'text', id: 'PasswordData', classname: 'auth-input', attr: 'placeholder=Пароль'}));
			
			AITDNIL.append(new elem('input', {type: 'text', id: 'PasswordDataNotInList', classname: 'auth-input', attr: 'placeholder=Пароль'}));
			
			AIC.append(AITD);
			
			AICNIL.append(AITDNIL);			  
		}
		
		AITD = new elem('td', {});
		
		AITD.append(new elem('button', {id: 'Login', classname: 'button -login', textcontent: 'Вход', addevent: 'click'}));
			
		AIC.append(AITD);
		
		return true;
		
	},
	
	MainContainer: function(Reload) {
		
		if (Reload) {
			
			QN.innerHTML = '';
	
			QH.innerHTML = '';
			
			QB.innerHTML = '';

		} else {
			
			document.querySelector('#HomePage').classList.add('hidden');
			
			document.querySelector('#TopContainer').classList.remove('hidden');
			
			document.querySelector('.top-project-name').textContent = PROJECT.Name;
			
			if (!! Object.keys(AUTHDATA).length) [].forEach.call(AUTHDATA.data, function(a, i) { document.querySelector('.top-data' + (i + 1)).textContent = a });
			
			else if ($_GET('P')) document.querySelector('.top-data1').textContent = $_GET('P');
			
		}
		
		if (PROJECT.Progress) document.querySelector('.top-progress').textContent = GetProgress(STATEINDEX);
		
		builder();
		
	},
	
	Page: function(PageId, Reload) {
		
		document.querySelector('#' + PageId).classList.remove('hidden');
		
		Result = this[PageId](Reload);
		
		return Result;
		
	}	
	
}

function MakeArray(String) {
	
	let A1 = String.split(',');
	
	let A2;
	
	let Result = [];
	
	for (let i in A1) {
		
		if (A1[i].indexOf('-') !== -1) {
			
			A2 = A1[i].split('-');
			
			for (let j = A2[0]; j <= A2[1]; j ++) Result.push(j.toString());
			
		} else Result.push(A1[i]);
		
	}
	
	return Result;
	
}

function NodeChange(Node, A) {
	
	switch (A) {
		
		case 'ON':
		
			if (Node.nodeName == 'TR') {
		
				[].forEach.call(Node.querySelectorAll('input'), function(i) { i.removeAttribute('data-not-required') });
					
				Node.classList.remove('hidden');
			
			}

		break;
		
		case 'OFF':
		
			if (Node.nodeName == 'TR') {
		
				if (typeof(Node.dataset.protect) == 'undefined') {
			
					[].forEach.call(Node.querySelectorAll('input'), function(i) { 
					
						i.setAttribute('data-not-required', '');
						
						if (i.type == 'checkbox' || i.type == 'radio') i.checked = false;
						
						if (i.type != 'checkbox' && i.type != 'radio') i.value = '';
						
					});
						
					Node.classList.add('hidden');
					
				}
			
			} else {

				[].forEach.call(QB.querySelectorAll('td[data-n="' + Node + '"]:not([data-protect])'), function(td) {
								
					let TargetInput = td.querySelectorAll('input:not([class*="text-module"], [data-protect])');
					
					if (TargetInput != null) [].forEach.call(TargetInput, function(inp) {
							
						inp.setAttribute('data-not-required', '');
						
						(inp.type == 'number' || inp.type == 'text') && (inp.value = '');
						
					});
				
					td.classList.add('hidden');
					
					QH.querySelector('th[data-n="' + Node + '"]').classList.add('hidden');
					
				});
				
			}
			
		break;
		
	}
	
}

function VisualRuleProcessing() {
	
	let EnList = [];
	
	let RD;
		
	for (let Rule in RESP.RuleData) {
		
		RD = RESP.RuleData[Rule];
	
		let Success = false;
		
		let InsertData = false;
			
		let ResponseGroups;

		let MatchIndex;
	
		let Response;
			
		let ResponseData = {Value: [], Data: []};
					
		switch (RD.Selection) {
			
			case 'ROW': case 'COL':
			
				if (RD.ResponseData.length > 1) {
					
					let TX = MakeArray(RD.Target);
					
					[].forEach.call(RD.ResponseData, function(RDV) {
						
						ResponseGroups = RDV.QResponse.match(/(?<val>[\d,]+)-?(?<data>.+)?/);
						
						if (ResponseGroups != null) {

							switch (RD.Operation) {
								
								case 'EQUAL': case 'NOT EQUAL':
								
									Success = (RD.Operation == 'EQUAL' && ResponseGroups.groups.val == TX[0]) || (RD.Operation == 'NOT EQUAL' && ResponseGroups.groups.val != TX[0]);
									
								break;
								
								case 'MORE': case 'NOT MORE':
								
									Success = (RD.Operation == 'MORE' && ResponseGroups.groups.val > TX[0]) || (RD.Operation == 'NOT MORE' && ResponseGroups.groups.val <= TX[0]);
									
								break;

								case 'LESS': case 'NOT LESS': 
								
									Success = (RD.Operation == 'LESS' && ResponseGroups.groups.val < TX[0]) || (RD.Operation == 'NOT LESS' && ResponseGroups.groups.val >= TX[0]);
									
								break;

								case 'CONTAINS': case 'NOT CONTAINS':
								
									let QX = MakeArray(ResponseGroups.groups.val);
									
									for (let i = 0; i < QX.length; i ++) {
										
										if ((RD.Operation == 'CONTAINS' && TX.includes(QX[i])) || (RD.Operation == 'NOT CONTAINS' && ! TX.includes(QX[i]))) {
											
											if (ResponseGroups.groups.data != null) {
												
												Response = ResponseGroups[0].match(/(?<num>\d+)-/);
												
												if (TX[0] == Response.groups.num) InsertData = true;
												
											}
											
											Success = true;
											
											break;
											
										}
										
									}
								
								break;
								
								case 'BETWEEN': case 'NOT BETWEEN':
								
									Success = (RD.Operation == 'BETWEEN' && ResponseGroups.groups.val >= TX[0] && ResponseGroups.groups.val <= TX[1]) || (RD.Operation == 'NOT BETWEEN' && (ResponseGroups.groups.val < TX[0] || ResponseGroups.groups.val > TX[1]));

								break;
								
								
							}
						
						}
						
						if (Success == true) {
							
							ResponseData.Value.push(RDV.QName.match(/_(\d+)$/, '')[1]);

							ResponseData.Data.push(InsertData && ResponseGroups.groups.data != null ? ResponseGroups.groups.data : '');
							
							Success = false;
							
							InsertData = false;
							
						}
						
					});
					
				} else {
					
					Response = RD.ResponseData[0].QResponse.split(',');
						
					[].forEach.call(Response, function(y) {
						
						ResponseGroups = y.match(/(\d+)-?(.+)?/);
						
						if (ResponseGroups != null) {
							
							ResponseData.Value.push(ResponseGroups[1]);
							
							ResponseData.Data.push(typeof(ResponseGroups[2]) != 'undefined' ? ResponseGroups[2] : '');
							
						}
						
					});
					
				}
				
				if (RD.Selection == 'ROW') {
					
					for (let i = 1; i <= RowsQuantity; i ++) {
						
						MatchIndex = ResponseData.Value.indexOf(i.toString());
			
						let Row = QB.querySelector('tr.string[data-string-index="' + i + '"]');

						if ((RD.Action == 'DISPLAY' && MatchIndex > -1) || (RD.Action == 'NOT DISPLAY' && MatchIndex == -1)) {
							
							if (ResponseData.Data[MatchIndex] != null && ResponseData.Data[MatchIndex] != '') {
								
								Row.querySelector('input').removeAttribute('data-prompt');

								Row.querySelector('.row').textContent = ResponseData.Data[MatchIndex];

							}
							
						} else NodeChange(Row, 'OFF');
						
					}
					
				} else {

					for (let i = 1; i <= ColsQuantity; i ++) {
						
						MatchIndex = ResponseData.Value.indexOf(i.toString());
						
						if ((RD.Action == 'DISPLAY' && MatchIndex > -1) || (RD.Action == 'NOT DISPLAY' && MatchIndex == -1)) {
							
							if (ResponseData.Data[MatchIndex] != null && ResponseData.Data[MatchIndex] != '') QH.querySelector('th[data-n="' + i + '"] div').textContent = ResponseData.Data[MatchIndex];

						} else NodeChange(i, 'OFF');
						
					}
					
				}
			
			break;
			
			case 'MSG':
				
				if (RD.Action != '') {
					
					if (RD.Target && RESP.RuleData.hasOwnProperty(RD.Target)) {

						let Exp = new RegExp('_' + RESP.RuleData[RD.Target].ResponseData[0].QResponse + '$');
						
						[].forEach.call(RD.ResponseData, function(x) {
						
							if (Exp.test(x.QName)) {
								
								let e = new elem('div', {classname: 'q-note', textcontent: RD.Action.replace('$', x.QResponse)});

								QN.append(e);
								
							}
							
						});
						
					} else {
						
						let e = new elem('div', {classname: 'q-note', textcontent: RD.Action.replace('$', RD.ResponseData[0].QResponse)});

						QN.append(e);
						
					}
					
				}
			
			break;
			
		}

	}
	
}

	
let elem = function(el, opt) {

	this.type = opt.type || false;
	
	this.id = opt.id || false;
	
	this.classname = opt.classname || false;
	
	this.name = opt.name || false;
	
	this.value = opt.value || false;
	
	this.title = opt.title || false;
	
	this.textcontent = opt.textcontent || false;
	
	this.innerhtml = opt.innerhtml || false;
	
	this.pattern = opt.pattern || false;
	
	this.placeholder = opt.placeholder  || false;
	
	this.attr = opt.attr || false;
	
	this.alt = opt.alt || false;

	this.attr && (this.attrlist = this.attr.split('*'));
	
	this.addevent = opt.addevent || false;
	
	this.src = opt.src || false;
	
	this.entity = document.createElement(el);
	
	this.type && (this.entity.type = this.type);
	
	this.classname && (this.entity.className = this.classname);
	
	this.title && (this.entity.title = this.title);
	
	if (this.addevent == 'click_') {
		
		this.entity.addEventListener('click', al);
		
	} else {
		
		this.addevent && typeof(this.addevent) == 'string' && (this.entity.addEventListener(this.addevent, listener))
		
	}
	
	if (this.addevent && typeof(this.addevent) == 'object') for (let y in this.addevent) this.entity.addEventListener(this.addevent[y], listener);
	
	if (this.attr) for (let y in this.attrlist) this.entity.setAttribute(this.attrlist[y].split('=')[0], this.attrlist[y].split('=')[1] || '');
	
	switch (el) {
		
		case 'div': case 'table': case 'tr': case 'td': case 'label': case 'span': case 'option': case 'datalist': case 'button': case 'dialog': case 'textarea': case 'select': case 'img':
		
			this.id && ((el == 'label') ? this.entity.setAttribute('for', this.id) : this.entity.id = this.id);
			
			this.textcontent && (this.entity.textContent = this.textcontent);
			
			this.innerhtml && (this.entity.innerHTML = this.innerhtml);
			
			if (this.src != null) this.entity.src = this.src;
			
			if (this.alt != null) this.entity.alt = this.alt;

		break;
		
		case 'input':
		
			this.id && (this.entity.id = this.id);
			
			this.name && (this.entity.name = this.name);
			
			this.value && (this.entity.value = this.value);

			switch (this.type) {
			
				case 'checkbox': case 'radio': case 'button':
				
					
				
				break;
				
				 case 'number': case 'text': case 'tel':
				
					this.textcontent && (this.entity.textContent = this.textcontent);
					
					this.pattern && (this.entity.pattern = this.pattern);
					
					this.placeholder && (this.entity.placeholder = this.placeholder);
				
				break;
			
			}
	
		break;
	
	}
	
	return this.entity;
	
}

function builder() {

	let TableContentHLT;

	let ColMix;
	
	let ColMixData = [];;

	let ColsData = [];
	
	let ColsDataM = [];
	
	let ColContentHLT;
	
	let WorkCol;
	
	let RowMix;
	
	let RowMixData = [];;
	
	let RowsData = [];
	
	let RowsDataM = [];
	
	let RowContentHLT;
	
	let WorkRow;

	let RowSection;
	
	let RowSectionData;
	
	let SingleInput;
    
	let SingleData;
	
	let SingleAttr;

	let SpecialChar;
	
	let OptData;
	
	let HasInput = true;

	let WorkObject;
	
	let Complex;
	
	let ComplexLength;
	
	let InputTypeData = QDATA.InputType != null ? QDATA.InputType.split('|') : null;

	let InputDataData = QDATA.InputData != null ? QDATA.InputData.split('|') : null;
	
	let InputAttrData = QDATA.InputAttr != null ? QDATA.InputAttr.split('|') : null;
	
	let TablePropertyData = QDATA.TableProperty != null ? QDATA.TableProperty.split('|') : null;

	let ColsContentData = QDATA.ColsContent != null ? QDATA.ColsContent.split('|') : null;

	let RowsContentData = QDATA.RowsContent != null ? QDATA.RowsContent.split('|') : null;
	
	// Получение настроек для ротации -------------------------------------------------------------------------------------------
	
	if (TablePropertyData != null) {
	
		[].forEach.call(TablePropertyData, function(x) {
			
			SpecialChar = x.match(/(?<Name>[^=]+)=?(?<Data>.+)?/);
			
			if (SpecialChar != null) {
				
				if (SpecialChar.groups.Name == 'RowMix') {
					
					RowMix = true;
					
					if (SpecialChar.groups.Data != null) RowMixData = MakeArray(SpecialChar.groups.Data);
					
				}
				
				if (SpecialChar.groups.Name == 'ColMix') {
					
					ColMix = true;
					
					if (SpecialChar.groups.Data != null) ColMixData = MakeArray(SpecialChar.groups.Data);
					
				}
	
			}
			
		});

	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	
	if (RowsContentData != null) {
		
		let RowsNoteData = QDATA.RowsNote != null ? QDATA.RowsNote.split('|') : null;
		
		let RowsClassData = QDATA.RowsClass != null ? QDATA.RowsClass.split('|') : null;
		
		let RowsPropertyData = QDATA.RowsProperty != null ? QDATA.RowsProperty.split('|') : null;

		[].forEach.call(RowsContentData, function(x, i) {
			
			WorkObject = {
			
				Name: x,
				
				Index: i + 1,
				
				Note: RowsNoteData != null ? (RowsNoteData.length > 1 ? RowsNoteData[i] : RowsNoteData[0]) : null,
				
				Class: RowsClassData != null ? (RowsClassData.length > 1 ? RowsClassData[i] : RowsClassData[0]) : 'row',
				
				Property: RowsPropertyData != null ? (RowsPropertyData.length > 1 ? RowsPropertyData[i] : RowsPropertyData[0]) : null,
				
				Input: QDATA.OutMark == null && InputTypeData ? (InputTypeData.length > 1 ? InputTypeData[i] : InputTypeData[0]) : null,
				
				Data: QDATA.OutMark == null && InputDataData ? (InputDataData.length > 1 ? InputDataData[i] : InputDataData[0]) : null,
				
				Attr: QDATA.OutMark == null && InputAttrData ? (InputAttrData.length > 1 ? InputAttrData[i] : InputAttrData[0]) : ''
	
			}
			
			if (RowMixData.length > 0 && RowMixData.includes((i + 1).toString())) RowsDataM.push(WorkObject);
			
			else RowsData.push(WorkObject);
			
		});
		
		if (RowMix) shuffle(RowsDataM.length > 0 ? RowsDataM : RowsData);
		
		RowsData = RowsDataM.concat(RowsData);
		
		RowsQuantity = RowsContentData.length;

	} else RowsQuantity = 1;
	
	if (ColsContentData != null) {
		
		let ColsNoteData = QDATA.ColsNote != null ? QDATA.ColsNote.split('|') : null;
		
		let ColsClassData = QDATA.ColsClass != null ? QDATA.ColsClass.split('|') : null;
		
		let ColsPropertyData = QDATA.ColsProperty != null ? QDATA.ColsProperty.split('|') : null;
		
		let ColsImgData = QDATA.ColsImg != null ? QDATA.ColsImg.split('|') : null;
		
		let FirstCol;

		[].forEach.call(ColsContentData, function(x, i) {
			
			WorkObject = {
			
				Name: x,
				
				Index: i,
				
				Note: ColsNoteData != null ? (ColsNoteData.length > 1 ? ColsNoteData[i] : ColsNoteData[0]) : null,
				
				Class: ColsClassData != null ? (ColsClassData.length > 1 ? ColsClassData[i] : ColsClassData[0]) : 'head',
				
				Property: ColsPropertyData != null ? (ColsPropertyData.length > 1 ? ColsPropertyData[i] : ColsPropertyData[0]) : null,
				
				Img: ColsImgData != null ? (ColsImgData.length > 1 ? ColsImgData[i] : ColsImgData[0]) : null,
				
				Input: QDATA.OutMark && InputTypeData ? (InputTypeData.length > 1 ? InputTypeData[i] : InputTypeData[0]) : null,
				
				Data: QDATA.OutMark && InputDataData ? (InputDataData.length > 1 ? InputDataData[i] : InputDataData[0]) : null,
				
				Attr: QDATA.OutMark && InputAttrData ? (InputAttrData.length > 1 ? InputAttrData[i] : InputAttrData[0]) : ''

			}
			
			if (i == 0) FirstCol = WorkObject;
			
			else {
				
				if (ColMixData.length > 0 && ColMixData.includes((i + 1).toString())) ColsDataM.push(WorkObject);
			
				else ColsData.push(WorkObject);
				
			}
			
		});
		
		if (ColMix) {
			
			shuffle(ColsDataM.length > 0 ? ColsDataM : ColsData);
			
			ColsData = ColsDataM.concat(ColsData);
			
			ColsData.unshift(FirstCol);
		
		} else ColsData.unshift(FirstCol);
		
		ColsQuantity = ColsContentData.length;

	} else ColsQuantity = RowsQuantity == 1 ? 1 : 2;

	if (ColsContentData == null && RowsContentData == null) {
		
		SingleInput = InputTypeData ? InputTypeData[0] : null;
		
		SingleData = InputDataData ? InputDataData[0] : null;
		
		SingleAttr = InputAttrData ? InputAttrData[0] : '';
		
	}
	
	// Включение журнала --------------------------------------------------------------------------------------------------------
	
	if (JF && + STATEINDEX >= + JF && PROJECT.Total != STATEINDEX) {
		
		let JD = new elem('div', {classname: 'j-container'});
		
		JD.append(new elem('span', {classname: 'j-name', innerhtml: 'Дневник ' + JOURNALINDEX}));
		
		QN.append(JD);
		
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	
	// Выделение элементов в названии вопроса -----------------------------------------------------------------------------------
	
    TableContentHLT = [...QDATA.TableContent.matchAll(/(?<Replace>\{(?<Hlt>[^\}]+)\})/g)];
	
	if (TableContentHLT.length > 0) [].forEach.call(TableContentHLT, function(x) {
		
		QDATA.TableContent = QDATA.TableContent.replace(x.groups.Replace, '<r class="hlt">' + x.groups.Hlt + '</r>');
		
	});
	
	// --------------------------------------------------------------------------------------------------------------------------
	
	// Выделение элементов в подписи вопроса -----------------------------------------------------------------------------------
	
	if (QDATA.TableNote) {
	
		TableNoteHLT = [...QDATA.TableNote.matchAll(/(?<Replace>\{(?<Hlt>[^\}]+)\})/g)];
		
		if (TableNoteHLT.length > 0) [].forEach.call(TableNoteHLT, function(x) {
			
			QDATA.TableNote = QDATA.TableNote.replace(x.groups.Replace, '<r class="note-hlt">' + x.groups.Hlt + '</r>');
			
		});
		
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	
	// Добавление названия и дополнительной подписи вопроса ---------------------------------------------------------------------
	
	if (QDATA.TableContent) QN.append(new elem('div', {classname: 'q-name', innerhtml: (QDATA.Num && QND ? QDATA.Num + '. ' : '') + QDATA.TableContent}));
	
	if (QDATA.TableNote) QN.append(new elem('div', {classname: 'q-expl', innerhtml: QDATA.TableNote}));
	
	// --------------------------------------------------------------------------------------------------------------------------
	
	// Добавление шапки таблицы -------------------------------------------------------------------------------------------------
	
	if (ColsContentData != null) for (let i = 0; i < ColsQuantity; i ++) {
		
		let TH = new elem('th', {classname: 'header-container', attr: 'data-n=' + ColsData[i].Index});
		
		// Выделение элементов в названии столбца -------------------------------------------------------------------------------
		
		ColContentHLT = [...ColsData[i].Name.matchAll(/(?<Replace>\{(?<Hlt>[^\}]+)\})/g)];
		
		if (ColContentHLT.length > 0) [].forEach.call(ColContentHLT, function(x) {
		
			ColsData[i].Name = ColsData[i].Name.replace(x.groups.Replace, '<r class="hlt">' + x.groups.Hlt + '</r>');
			
		});
		
		// ----------------------------------------------------------------------------------------------------------------------
		
		TH.appendChild(new elem('div', {classname: ColsData[i].Class, innerhtml: ColsData[i].Name}));
		
		if (ColsData[i].Note != null) {

			SpecialChar = ColsData[i].Note.match(/^.*(?<Replace>\$(?<Var>.+)\$).*/);
			
			TH.appendChild(new elem('div', {classname: 'note', textcontent: SpecialChar != null ? ColsData[i].Note.replaceAll(SpecialChar.groups.Replace, RULESDATA[SpecialChar.groups.Var.replace(/\./g, '_')].ResponseData[0].QResponse) : ColsData[i].Note}));

		}

		QH.appendChild(TH);
		
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	
	// Добавление строк таблицы -------------------------------------------------------------------------------------------------
		
	for (let RowIndex = 0, id = 0; RowIndex < RowsQuantity; RowIndex ++) {
		
		if (RowsData.length) {
			
			WorkRow = RowsData[RowIndex];
			
		}
		
		// Добавление разделителя строк на секции -------------------------------------------------------------------------------
		
		WorkRow && (RowSectionData = WorkRow.Name.match(/^(?<Replace>#(?<Section>.*)#).*/));
		
		if (RowSectionData != null) {
			
			WorkRow.Name = WorkRow.Name.replace(RowSectionData.groups.Replace, '');
			
			RowSection = new elem('tr', {classname: 'Section-string'});
			
			RowSection.append(new elem('td', {attr: 'colspan=' + ColsQuantity, innerhtml: RowSectionData.groups.Section}));
			
			QB.appendChild(RowSection);
			
		}
		
		// ----------------------------------------------------------------------------------------------------------------------
		
		let TR = new elem('tr', {classname: 'string', attr: 'data-string-index=' + (WorkRow ? WorkRow.Index : RowIndex) + (WorkRow && WorkRow.Property ? '*' + WorkRow.Property : '')});
		
		let PromptEl;
		
		// Добавление столбцов таблицы ------------------------------------------------------------------------------------------
		
		for (let ColIndex = 0; ColIndex < ColsQuantity; ColIndex ++) {
			
			if (ColsData.length) {

				WorkCol = ColsData[ColIndex];
				
			}
			
			let TD = new elem('td', {classname: 'input-container', attr: 'data-n=' + (WorkCol ? WorkCol.Index : ColIndex) + (WorkCol && WorkCol.Attr != '' ? '*' + WorkCol.Attr : '')});
			
			if (ColIndex > 0 || RowsQuantity == 1) {
				
				id ++;
				
				let WorkInputType = WorkRow && WorkRow.Input ? WorkRow.Input : (WorkCol && WorkCol.Input ? WorkCol.Input : SingleInput);
				
				if (WorkInputType == null) {
				
					HasInput = false;
					
					DATACOMPLETED.hasOwnProperty(QDATA.Num) || (DATACOMPLETED[QDATA.Num] = ['NODATA', [], '']);
					
					break;
					
				}
				
				let WorkInputData = WorkRow ? WorkRow.Data : (WorkCol ? WorkCol.Data : SingleData);
				
				let WorkInputAttr = SingleAttr ? SingleAttr : '';
				
				if (WorkRow && WorkRow.Attr) WorkInputAttr += (WorkInputAttr != '' ? '*' : '') + WorkRow.Attr;
				
				if (WorkCol && WorkCol.Attr) WorkInputAttr += (WorkInputAttr != '' ? '*' : '') + WorkCol.Attr;
				
				if (WorkRow && WorkRow.Property) WorkInputAttr += (WorkInputAttr != '' ? '*' : '') + WorkRow.Property;
				
				if (WorkCol && WorkCol.Property) WorkInputAttr += (WorkInputAttr != '' ? '*' : '') + WorkCol.Property;

				if (WorkInputAttr != '') {
				
					SpecialChar = WorkInputAttr.match(/^.*(?<Replace>\$(?<Var>.+)\$).*/);
					
					if (SpecialChar != null) WorkInputAttr = WorkInputAttr.replaceAll(SpecialChar.groups.Replace, RULESDATA[SpecialChar.groups.Var.replace(/\./g, '_')].ResponseData[0].QResponse);
					
				}
				
				if (WorkInputType == 'radio' || WorkInputType == 'checkbox') {
					
					Complex = WorkInputData != null ? WorkInputData.split('*') : null;

					ComplexLength = Complex ? Complex.length : 1;
					
				} else ComplexLength = 1;
				
				if (WorkInputType == 'select') OptData = WorkInputData != null ? WorkInputData.split('*') : null;
				
				for (let ComplexIndex = 0; ComplexIndex < ComplexLength; ComplexIndex ++) {
					
					let WorkId = id + (ComplexLength > 1 ? '.' + ComplexIndex : '');
					
					let WorkValue = Complex ? ComplexIndex + 1 : (QDATA.OutMark == null ? (WorkRow ? WorkRow.Index : RowIndex) : (WorkCol ? WorkCol.Index : ColIndex));
					
					let WorkOutMark = WorkInputType == 'file' ? 'file' : QDATA.Num;
					
					if (WorkInputType == 'checkbox' || WorkInputType == 'radio') {
						
						if (ColsQuantity > 2) {
							
							WorkOutMark += '.' + (QDATA.OutMark == null ? (WorkCol ? WorkCol.Index : '') : (WorkRow ? WorkRow.Index : ''));
							
						} else if (InputTypeData.length > 1) {
							
							WorkOutMark += (WorkRow ? '.' + WorkRow.Index : '');
							
						}
						
					} else {
						
						if (ColsQuantity > 2) {
							
							WorkOutMark += '.' + (QDATA.OutMark == null ? (WorkCol ? WorkCol.Index : '') : (WorkRow ? WorkRow.Index : ''));
							
						}
						
						WorkOutMark += QDATA.OutMark == null ? (WorkRow ? '.' + WorkRow.Index : '') : (WorkCol ? '.' + WorkCol.Index : '');
						
					}
					
					switch (WorkInputType) {
						
						case 'textarea':
						
							TD.appendChild(new elem('textarea', {
						
								id: WorkId,
								
								attr: 'data-out=' + WorkOutMark + '*required*data-display-error=bottom*cols=70*data-n=' + (WorkCol ? WorkCol.Index : ColIndex) + (WorkInputAttr ? '*' + WorkInputAttr : '')
								
							}));
						
						break;
						
						case 'select':
						
							if (OptData != null) {
								
								let SEL = new elem('select', {id: WorkId, attr: 'data-out=' + WorkOutMark, addevent: 'change'});
								
								SEL.appendChild(new elem('option', {textcontent: 'Выбрать'}));
								
								[].forEach.call(OptData, function(Opt, OptIndex) {
									
									SEL.appendChild(new elem('option', {textcontent: Opt.replace(/\[t\]/, '')}));
									
									/\[t\]/.test(Opt) && (SEL.setAttribute('data-tmi', OptIndex));
									
								});

								TD.appendChild(SEL);
								
							}
						
						break;
						
						case 'text': case 'number': case 'tel':
						
							TD.appendChild(new elem('input', {
							
							type: WorkInputType, 
							
							id: WorkId,  
							
							value: WorkInputType == 'tel' ? '+7(___)___-__-__' : '',
							
							placeholder: WorkInputType == 'tel' ? '+7(___)___-__-__' : '',
							
							pattern: WorkInputType == 'tel' ? '\\+7\\s?[\\(]{0,1}9[0-9]{2}[\\)]{0,1}\\s?\\d{3}[-]{0,1}\\d{2}[-]{0,1}\\d{2}' : '',
							
							attr: 'data-out=' + WorkOutMark + '*required*data-n=' + (WorkCol ? WorkCol.Index : ColIndex) + (WorkInputAttr ? '*' + WorkInputAttr : ''),
							
							addevent: ['input', 'keyup']}));
						
						break;
						
						case 'checkbox': case 'radio':

							TD.appendChild(new elem('input', {
							
							type: WorkInputType, 
							
							id: WorkId, 
							
							name: WorkOutMark, 
							
							value: WorkValue, 
							
							attr: 'data-out=' + WorkOutMark + '*data-n=' + (WorkCol ? WorkCol.Index : ColIndex) + (WorkInputAttr ? '*' + WorkInputAttr : '') + (PromptEl ? '*data-prompt' : ''),
							
							addevent: 'change'}));
							
						break;
					
					}
					
					if (/text|tel/.test(WorkInputType)) TD.appendChild(new elem('span', {}));
					
					if (WorkInputType == 'radio' || WorkInputType == 'checkbox') TD.appendChild(new elem('label', {classname: WorkInputType + (ComplexLength > 1 ? ' single-string' : ''), textcontent: (Complex ? Complex[ComplexIndex] : ''), attr: 'for=' + WorkId}));
					
					if (WorkInputType == 'text' && /data-progress=line/.test(WorkInputAttr)) TD.appendChild(new elem('div', {classname: 'progress-container', attr: 'data-for=' + WorkId}));
					
					DATACOMPLETED.hasOwnProperty(WorkOutMark) || (DATACOMPLETED[WorkOutMark] = [WorkInputType, [], '']);
					
					if (PromptEl) PTDATA.hasOwnProperty(WorkOutMark) || (PTDATA[WorkOutMark] = {});
					
				}
				
			} else {
				
				PromptEl = WorkRow.Name.match(/^Друг(ое$|ие$|ая$|ой$)|\[(t|n)\]/);
				
				WorkRow.Name = WorkRow.Name.replace(/\[t\]|\[n\]/, '');
				
				// Подстановка ответа в название строки -------------------------------------------------------------------------
				
				SpecialChar = WorkRow.Name.match(/^.*(?<Replace>\$(?<Var>.+)\$).*/);
				
				if (SpecialChar != null) WorkRow.Name = WorkRow.Name.replaceAll(SpecialChar.groups.Replace, RULESDATA[SpecialChar.groups.Var.replace(/\./g, '_')].ResponseData[0].QResponse);
				
				// --------------------------------------------------------------------------------------------------------------
				
				// Выделение элементов в названии строки ------------------------------------------------------------------------
				
				RowContentHLT = [...WorkRow.Name.matchAll(/(?<Replace>\{(?<Hlt>[^\}]+)\})/g)];
		
				if (RowContentHLT.length > 0) [].forEach.call(RowContentHLT, function(x) {
				
					WorkRow.Name = WorkRow.Name.replace(x.groups.Replace, '<r class="hlt">' + x.groups.Hlt + '</r>');
					
				});
				
				// --------------------------------------------------------------------------------------------------------------
				
				TD.classList.add('row-head');
				
				TD.appendChild(new elem('div', {classname: WorkRow.Class, innerhtml: WorkRow.Name}));

				if (WorkRow.Note) {
					
					// Подстановка ответа в подпись строки ----------------------------------------------------------------------

					SpecialChar = WorkRow.Note.match(/^.*(?<Replace>\$(?<Var>.+)\$).*/);

					TD.appendChild(new elem('div', {classname: 'row-note', textcontent: SpecialChar != null ? WorkRow.Note.replaceAll(SpecialChar.groups.Replace, RULESDATA[SpecialChar.groups.Var.replace(/\./g, '_')].ResponseData[0].QResponse) : WorkRow.Note}));
					
					// ----------------------------------------------------------------------------------------------------------

				}
				
			}
			
			TR.appendChild(TD);
			
		}
		
		if (HasInput) QB.appendChild(TR);
		
	}

	// --------------------------------------------------------------------------------------------------------------------------
	
	if (STATEINDEX == '1') {
		
		document.querySelector('#Back').classList.add('hidden');
		
		document.querySelector('#Submit').textContent = HasInput ? 'Сохранить ответ и перейти к следующему вопросу' : 'Следующий вопрос';

	} else if (+ STATEINDEX < + PROJECT.Total) {
		
		if (HISTORY && + STATEINDEX > 1) document.querySelector('#Back').classList.remove('hidden');
		
		document.querySelector('#Submit').textContent = HasInput ? 'Сохранить ответ и перейти к следующему вопросу' : 'Следующий вопрос';
  
	} else if (STATEINDEX == PROJECT.Total) {
		
		document.querySelector('#Submit').classList.add('hidden');
		
		document.querySelector('#Back').classList.add('hidden');
		
	}
  
	if (RESP.HistoryData) for (let i in RESP.HistoryData) {
		
		let INM = i.replace(/_/g, '.');
		
		let IEL = document.querySelector('[data-out="' + INM + '"]');
		
		if (IEL != null) HistoryDataFill(IEL.type, INM, RESP.HistoryData[i].split(','));
		
	}
	
}

function HistoryDataFill(Type, DataOut, DataValue) {
	
	let DataArray;
	
	let Element;
	
	switch (Type) {
		
		case 'text': case 'number': case 'tel': case 'textarea': document.querySelector('[data-out="' + DataOut + '"]').value = DataValue;
		
		break;
		
		case 'select-one':
		
			Element = document.querySelector('[data-out="' + DataOut + '"]');
			
			if (DataValue[0] != '') {	
			
				DataArray = DataValue[0].match(/(\d+)-?(.+)?/);
		
				Element.selectedIndex = DataArray[1];
				
				if (typeof(DataArray[2]) != 'undefined') {
					
					Element.parentNode.lastChild.value = DataArray[2].replace(/\//g, ',');
						
					Element.parentNode.lastChild.classList.add('-active');
					
				}
				
			}
		
		break;
		
		case 'checkbox':
	                
			if (DataValue[0] != '') {	
			
				[].forEach.call(DataValue, function(x) {
					
					DataArray = x.match(/(?<val>\d+)-?(?<txt>.+)?/);
					
					Element = document.querySelector('[data-out="' + DataOut + '"][value="' + DataArray.groups.val + '"]');
					
					Element.checked = true;
					
					if (typeof(DataArray.groups.txt) != 'undefined') {
						
						Element.parentNode.lastChild.title = DataArray.groups.txt.replace(/\//g, ',');
						
						PTDATA[DataOut][DataArray.groups.val] = DataArray.groups.txt.replace(/\//g, ',');
						
					}
					
				});
				
			}
			
		break;
		
		case 'radio':
	                
			if (DataValue[0] != '') {
				
				DataArray = DataValue[0].match(/(?<val>\d+)-?(?<txt>.+)?/);

				Element = document.querySelector('[data-out="' + DataOut + '"][value="' + DataArray.groups.val + '"]');
				
				Element.checked = true;
				
				if (typeof(DataArray.groups.txt) != 'undefined') {
					
					Element.parentNode.lastChild.title = DataArray.groups.txt.replace(/\//g, ',');
					
					PTDATA[DataOut][DataArray.groups.val] = DataArray.groups.txt.replace(/\//g, ',');
					
				}
			
			}
			
		break;
		
	}
	
}

function progressBar(el) {
	
	let delta = Math.round(100 / (+ el.maxLength / el.value.length));
	
	let bg;
	
	switch (el.dataset.progress) {
		
		case 'B':
		
			bg = typeof(el.dataset.progressBg) != 'undefined' ? el.dataset.progressBg : '00ff4d50';
		
			el.style.background = 'linear-gradient(90deg, ' + bg + ' ' + delta + '%, transparent ' + delta + '%, transparent 100%)';
		
		break;
		
		case 'L':
		
			bg = typeof(el.dataset.progressBg) != 'undefined' ? el.dataset.progressBg : '00ff4d80';
		
			document.querySelector('div[data-for="' + el.id + '"]').style.width = delta + '%';
			
			document.querySelector('div[data-for="' + el.id + '"]').style.backgroundColor = bg;
		
		break;
		
	}
	
}

function listener(e) {
	
	let el = e.target;
	
	let A = document.querySelector('table.main-table');
	
	let FD = new FormData();
	
	let pool;
	
	document.querySelector('#EContainer') && (document.querySelector('#EContainer').remove());
	
	if (document.querySelector('#WarnMarker') != null) {

		document.querySelector('#WarnMarker').remove();
		
		document.querySelector('#Submit').textContent = 'Сохранить ответ и перейти к следующему вопросу';
		
		document.querySelector('#Submit').classList.remove('-warn');
		
	}
	
	switch (e.type) {
		
		case 'keyup':
		
			if (/Enter|NumpadEnter/.test(e.code)) document.querySelector('#Submit').click();
		
		break;
		
		case 'change' :
		
			switch (el.type) {
				
				case 'checkbox':
				
					if (el.id == 'NotInList') {

						if (el.checked) document.querySelector('#NotInListTable').classList.remove('hidden');
						
						else document.querySelector('#NotInListTable').classList.add('hidden');
						
						[].forEach.call(document.querySelectorAll('#AuthInputContainer input'), function(x) {
							
							if (el.checked) x.setAttribute('Disabled', '');
							
							else x.removeAttribute('disabled');
							
						});
						
					} else {
						
						if (el.checked) {

							pool = QDATA.OutMark == '1' ? el.parentNode.parentNode.querySelectorAll('input[type="checkbox"]') : A.querySelectorAll('input[data-n="' + el.dataset.n + '"]');
	
							if (el.hasAttribute('data-only')) {
							
								[].forEach.call(pool, function(x) {
							
									if (x != el) {

										x.checked = false;
										
										if (x.hasAttribute('data-prompt')) {
											
											delete PTDATA[x.dataset.out][x.value];
						
											x.parentNode.querySelector('label').title = '';
											
										}
										
									}
								
								})
								
							} else {
								
								[].forEach.call(pool, function(x) { 
								
									if (x.hasAttribute('data-only')) {

										x.checked = false;
										
										if (x.hasAttribute('data-prompt')) {
											
											delete PTDATA[x.dataset.out][x.value];
						
											x.parentNode.querySelector('label').title = '';
											
										}
										
									}
									
								});
								
							}
	
							if (el.hasAttribute('data-single')) {

								pool = el.dataset.single == 'row' ? el.parentNode.parentNode.querySelectorAll('input[type="checkbox"], input[type="radio"]') : A.querySelectorAll('input[data-n="' + el.dataset.n + '"]');
								
								[].forEach.call(pool, function(x) { x == el || (x.checked = false) });
	
							}
		
							if (el.hasAttribute('data-equal')) {
								
								pool = QDATA.OutMark == '1' ? el.parentNode.parentNode.querySelectorAll('input[type="checkbox"]:checked') : A.querySelectorAll('input[data-n="' + el.dataset.n + '"]:checked');
								
								if (el.dataset.equal < pool.length) {

									let next = false;
									
									let first;
									
									[].forEach.call(pool, function(x, i) {
										
										if (next) {
											
											x.checked = false;
											
											next = false;
											
											if (x.hasAttribute('data-prompt')) {
												
												delete PTDATA[x.dataset.out][x.value];
						
												x.parentNode.querySelector('label').title = '';;
												
											}
											
										}
										
										if (x == el && i < el.dataset.equal) next = true;
										
										else if (i == 0) first = x;
										
										if (x == el && i == el.dataset.equal) first.checked = false;

									});
									
								}

							}
							
							if (el.hasAttribute('data-prompt')) {
								
								al('SinglePrompt').then(function(x) {
									
									if (PROMPTRESULT) {
										
										if (PROMPTRESULT.value.trim() == '') {

											delete PTDATA[el.dataset.out][el.value];
											
											el.parentNode.querySelector('label').title = '';
											
											el.checked = false;
											
										} else {
											
											PTDATA[el.dataset.out][el.value] = PROMPTRESULT.value;
											
											el.parentNode.querySelector('label').title = PROMPTRESULT.value;
											
										}
										
									} else el.checked = false;
									
								}).catch(function(e) {
									
									el.checked = false;
									
									console.log(e);
									
								});
								
							}
		
						} else if (el.hasAttribute('data-prompt')) {
												
							delete PTDATA[el.dataset.out][el.value];
							
							el.parentNode.querySelector('label').title = '';
							
						}
					
					}

				break;
				
				case 'radio':
				
					if (el.hasAttribute('data-single')) [].forEach.call(el.parentNode.parentNode.querySelectorAll('input[type="checkbox"], input[type="radio"]'), function(x) { x == el || (x.checked = false) });
			
					if (el.hasAttribute('data-prompt')) {
						
						if (PTDATA.hasOwnProperty(el.dataset.out)) {
							
							PTDATA[el.dataset.out] = {};
							
							[].forEach.call(A.querySelectorAll('input[name="' + el.dataset.out + '"]+label'), function(x) { x.title = '' });
							
						}
						
						al('SinglePrompt').then(function(x) {
									
							if (PROMPTRESULT) {
								
								if (PROMPTRESULT.value.trim() == '') {

									el.parentNode.querySelector('label').title = '';
									
									el.checked = false;
									
								} else {
									
									PTDATA[el.dataset.out][el.value] = PROMPTRESULT.value;
									
									el.parentNode.querySelector('label').title = PROMPTRESULT.value;
									
								}
								
							} else el.checked = false;
							
						}).catch(function(e) {
							
							el.checked = false;
							
							console.log(e);
							
						});
	
					} else if (PTDATA.hasOwnProperty(el.dataset.out)) {
						
						PTDATA[el.dataset.out] = {};
						
						[].forEach.call(A.querySelectorAll('input[name="' + el.dataset.out + '"]+label'), function(x) { x.title = '' });
						
					}
					
					if (el.hasAttribute('data-single')) {

						pool = el.dataset.single == 'row' ? el.parentNode.parentNode.querySelectorAll('input[type="checkbox"], input[type="radio"]') : A.querySelectorAll('input[data-n="' + el.dataset.n + '"]');
						
						[].forEach.call(pool, function(x) { x == el || (x.checked = false) });
	
					}

				break;
				
				case 'file':
				
					let fi = document.querySelector('.file-info');
				
					while (fi.firstChild) fi.removeChild(fi.firstChild);
				
					[].forEach.call(el.files, function(x) {
					
						let size = Math.floor(x.size / 1048576) > 0 ? ((x.size / 1048576).toFixed(1) + 'Mb') : ((x.size / 1024).toFixed(1) + 'Kb');
						
						fi.appendChild(new elem('div', {textcontent: x.name + ' (' + size + ')'}));
						
					});
				
				break;
				
				case 'select':
				
					if (el.dataset.tmi != null) {
				
						if (el.selectedIndex == el.dataset.tmi) {
							
							el.parentNode.lastChild.classList.add('-active');
							
							el.parentNode.lastChild.focus();
							
						} else {
							
							el.parentNode.lastChild.classList.remove('-active');
							
						}
						
					}
					
				break;
				
			}
		
		break;
		
		case 'input':
		
			switch (el.type) {
				
				case 'number':
				
					if (el.value && ! /^0.+|\D/g.test(el.value)) {
				
						el.hasAttribute('min') && (el.value < + el.min && (el.value = el.value.substr(0, el.value.length - 1)));
						
						el.hasAttribute('max') && (el.value > + el.max && (el.value = el.value.substr(0, el.value.length - 1)));
						
					} else el.value = '';

				break;
				
				case 'text':
				
					el.value = el.value.replace(/[\/\\`~^]/g, '');
				
					el.dataset.maxLength != 'undefined' && (el.value.length > + el.dataset.maxLength && (el.value = el.value.substr(0, el.dataset.maxLength)));
					
					el.dataset.except != 'undefined' && (el.value = el.value.replace(el.dataset.except, ''));
					
					if (el.dataset.progress != 'undefined' && el.dataset.maxLength != 'undefined') progressBar(el);

				break;
				
				case 'tel':
				
					mask(el);
				
				break;
			}
		
		break;
		
		case 'blur':
		
			switch (el.type) {

				case 'text':
				
					switch (el.id) {
						
						case 'AuthIL1': 
						
							LOGINDEX = FILEDATA[0].fieldData.flat().indexOf(el.value);
							
							let domList = document.querySelector('#AuthList2');
						
							if (LOGINDEX != -1) { // not empty
								
								while (domList.firstChild) domList.removeChild(domList.firstChild);
								
								[].forEach.call(FILEDATA[1].fieldData[LOGINDEX], function(x) {
									
									let optF = new elem('option', {textcontent: x});
						
									domList.append(optF);
									
								});
								
							} else if (el.value.trim() == '') { // empty
								
								while (domList.firstChild) domList.removeChild(domList.firstChild);
								
								[].forEach.call(FILEDATA[1].fieldData.flat(), function(x) {
									
									let optE = new elem('option', {textcontent: x});
						
									domList.append(optE);
									
								});
								
							}
				
						break;
						
						case 'AuthIL2': 
						
							LOGINDEX = -1;
								
							for (let i = 0; i < FILEDATA[1].fieldData.length; i ++) if (FILEDATA[1].fieldData[i].includes(el.value)) {
									
								LOGINDEX = i;
								
								break;
								
							}
							
							if (LOGINDEX != -1) document.querySelector('#AuthIL1').value = FILEDATA[0].fieldData[LOGINDEX][0];

						break;
					
					}
				
				break;
			
			}
		
		break;
		
		case 'load':
		
			el.classList.remove('transparent');
		
		break;
		
		case 'click':
		
			if (! el.hasAttribute('id')) while ((el = el.parentNode) && ! el.hasAttribute('id'));
		
			switch (el.id) {
				
				case 'Submit':
				
					if (Object.keys(DATACOMPLETED).length) {
					
						let E = false;
						
						let WorkSet;
						
						let NODATA = DATACOMPLETED.hasOwnProperty(null);
						
						for (let i in DATACOMPLETED) DATACOMPLETED[i][1] = [];
						
						if (! NODATA) for (let i in DATACOMPLETED) { // by data-out 
							
							switch (DATACOMPLETED[i][0]) {
								
								case 'checkbox':
								
									WorkSet = A.querySelectorAll('[data-out="' + i + '"]');
									
									let CheckboxError = false;
									
									E = VALID.Check(WorkSet);
									
									if (E && typeof(E[0]) != 'string') {
										
										[].forEach.call(E, function(x) {
											
											if (x.dataset.prompt != null) {
												
												DATACOMPLETED[i][1].push(x.value + '-' + PTDATA[x.dataset.out][x.value].replace(/,/g, '/'));

											} else if (x.dataset.autoFill != null) {
												
												DATACOMPLETED[i][1].push(x.value + '-' + x.parentNode.parentNode.querySelector('.row').textContent);
												
											} else DATACOMPLETED[i][1].push(x.value);
											
										});
										
										E = CheckboxError ? CheckboxError : false;
										
									}

								break;
								
								case 'radio':
								
									WorkSet = A.querySelectorAll('[data-out="' + i + '"]');
									
									let RadioValue = '';
									
									E = VALID.Check(WorkSet);
									
									if (E && typeof(E[0]) != 'string') {
										
										if (typeof(E[0].dataset.prompt) != 'undefined') {
	
											RadioValue = E[0].value + '-' + PTDATA[E[0].dataset.out][E[0].value].replace(/,/g, '/');
	
										} else RadioValue = E[0].value;
										
										if (RadioValue != '') {
											
											DATACOMPLETED[i][1].push(RadioValue + (E[0].dataset.autoFill != null ? ('-' + E[0].parentNode.parentNode.querySelector('.row').textContent) : ''));
											
											E = false;
											
										}
										
									}

								break;
								
								case 'number':
	
									WorkSet = A.querySelector('[data-out="' + i + '"]');
									
									if (! /^$|^0.+$|\D+/g.test(WorkSet.value)) {
										
										E = VALID.Check(WorkSet);
										
										if (! E) DATACOMPLETED[i][1].push(WorkSet.value);
										
									} else if (WorkSet.hasAttribute('disabled') || WorkSet.hasAttribute('data-not-required') || WorkSet.hasAttribute('data-default')) {
										
										if (WorkSet.hasAttribute('data-default')) {
											
											DATACOMPLETED[i][1].push(WorkSet.dataset.default);
											
										} else DATACOMPLETED[i][1].push('');
										
										//if (! E && WorkSet.hasAttribute('data-limit')) E = VALID.Check(WorkSet, 'Limit');
										
										//if (! E && WorkSet.hasAttribute('data-total')) E = VALID.Check(WorkSet, 'Total');
										
									} else E = ['Выделенное поле заполнено некорректно.<br>Необходимо заполнить это поле.', [WorkSet]];
								
								break;
								
								case 'text':
								
									WorkSet = A.querySelector('[data-out="' + i + '"]'); 
									
									E = VALID.Check(WorkSet);
									
									if (! E) DATACOMPLETED[i][1].push(WorkSet.value);
								
								break;
								
								case 'file':
									
									WorkSet = A.querySelector('input[type="file"]');
									
									if (WorkSet.files.length) {
										
										[].forEach.call(WorkSet.files, function(x) { DATACOMPLETED[i][1].push(x.name); });

									} 
									
								break;
								
								case 'tel':
								
									WorkSet = A.querySelector('[data-out="' + i + '"]'); 
								
									if (! /_/.test(WorkSet.value)) {
										
										DATACOMPLETED[i][1].push(WorkSet.value);
										
									} else E = ['Выделенное поле заполнено некорректно.<br>Необходимо правильно заполнить это поле', [WorkSet]];

								break;
								
								case 'textarea':
								
									WorkSet = A.querySelector('[data-out="' + i + '"]');
									
									if (WorkSet.hasAttribute('data-not-required') || WorkSet.value) {
										
										DATACOMPLETED[i][1].push(WorkSet.value);
										
									} else E = ['Выделенное поле заполнено некорректно.<br>Необходимо заполнить это поле.', [WorkSet]];
								
								break;
								
								case 'select':
								
									WorkSet = A.querySelector('[data-out="' + i + '"]');
									
									if (WorkSet.hasAttribute('data-not-required') || WorkSet.selectedIndex > 0) {
										
										if (WorkSet.dataset.tmi != null) {
											
											if (WorkSet.selectedIndex == WorkSet.dataset.tmi) {
												
												let SelectText = WorkSet.parentNode.lastChild;
												
												if (SelectText.value) {
													
													DATACOMPLETED[i][1].push(WorkSet.selectedIndex + '-' + SelectText.value.replace(/,/g, '/'));
													
													DATACOMPLETED[i][2] = WorkSet.querySelector('option:nth-child(' + (WorkSet.selectedIndex + 1) + ')').value;
													
												} else E = ['Выделенное поле заполнено некорректно.<br>Необходимо заполнить это поле.', [WorkSet]];	
												
											} else {
												
												DATACOMPLETED[i][1].push(WorkSet.selectedIndex);
												
												DATACOMPLETED[i][2] = WorkSet.querySelector('option:nth-child(' + (WorkSet.selectedIndex + 1) + ')').value;
												
											}
											
										} else {
											
											DATACOMPLETED[i][1].push(WorkSet.selectedIndex);
											
											DATACOMPLETED[i][2] = WorkSet.querySelector('option:nth-child(' + (WorkSet.selectedIndex + 1) + ')').value;
											
										}
										
									} else E = ['Выделенное поле заполнено некорректно.<br>Необходимо выбрать в этом поле.', [WorkSet]];
								
								break;
								
								case 'NODATA': DATACOMPLETED[i][1].push('');
								
								break;
								
								default: DATACOMPLETED[i][1].push(A.querySelector('[data-out="' + i + '"]').value);
								
								break;
								
							}
							
							if (E) break;
							
						}
						
						if (! E && TRACKER.length) {
							
							WorkSet = [];
							
							[].forEach.call(TRACKER, function(x) { WorkSet.push(document.querySelector('img[data-track-number="' + x + '"')) });
							
							E = ['Необходимо просмотреть все изображения.', WorkSet];
							
						}

						if (E) { // display error
						
							if (false) {
							
								document.querySelector('#EContainer') && (document.querySelector('#EContainer').remove());
								
								let veo;
								
								[].forEach.call(E[1], function(x) { if (x.dataset.notRequired != '' && veo == null) veo = x });
								
								let pn = veo.dataset.displayErrorObject && veo.dataset.displayErrorObject == 'table' ? A : veo.parentNode;
								
								let displayError = veo.dataset.displayError || 'right';
								
								let ed;
								
								switch (displayError) {
									
									case 'right':
										
										ed = new elem('div', {id: 'EContainer', classname: 'e-container-r', innerhtml: E[0], addevent: 'click'});
								
										QB.append(ed);
										
										ed.style.left = pn.offsetLeft + pn.offsetWidth + 10 + 'px';
										
										ed.style.top = pn.offsetTop + pn.offsetHeight / 2 - ed.offsetHeight / 2 + 'px';
										
									break;
									
									case 'bottom':
										
										ed = new elem('div', {id: 'EContainer', classname: 'e-container-b', innerhtml: E[0], addevent: 'click'});
								
										QB.append(ed);
										
										ed.style.left = pn.offsetLeft + pn.offsetWidth / 2 - ed.offsetWidth / 2 + 'px';
										
										ed.style.top = pn.offsetTop + pn.offsetHeight + 10 + 'px';
										
									break;
									
								}
							
							} else {

								let Top = 0;
								
								let Left = 0;
								
								let Width = 0;
								
								let Height = 0;
								
								let Offset;
								
								[].forEach.call(E[1], function(x, i) {
									
									Offset = getOffset(x.parentNode);

									Left = Left || Offset.Left;
										
									Top = Top || Offset.Top;
										
									if (Offset.Left && Offset.Width) Width = Offset.Left + Offset.Width - Left;
										
									if (Offset.Top && Offset.Height) Height = Offset.Top + Offset.Height - Top;
									
								});
								
								ed = new elem('div', {id: 'WarnMarker', classname: '-warnmarker', addevent: 'click'});
								
								document.querySelector('#MainContainer').append(ed);
								
								ed.style.left = Left + 'px';
								
								ed.style.top = Top - 30 + 'px';//- document.querySelector('#MainContainer').scrollTop + 'px';
								
								ed.style.width = Width + 'px';
								
								ed.style.height = Height + 'px';
								
								document.querySelector('#Submit').classList.add('-warn');
								
								document.querySelector('#Submit').innerHTML = E[0];
								
							}
		
						} else { // q send online --------------------------------------------------------------------------------------------------------------------------
							
							if (CANSEND) {
								
								CANSEND = false;
								
								FD.append('ProjectId', PROJECT.id);
								
								FD.append('ProjectLimiting', PROJECT.Limiting);
								
								FD.append('UserId', UID);
								
								FD.append('StateIndex', STATEINDEX);
								
								if (JF && + STATEINDEX >= + JF) FD.append('JournalIndex', JOURNALINDEX);
								
								FD.append('Total', PROJECT.Total);
								
								if (HISTORY) FD.append('HistoryState', JSON.stringify(HIO));
								
								if (JF && STATEINDEX == JL) FD.append('JournalState', (JOURNALINDEX == JC ? 'END' : JF));

								if (RULES.DIRECT && RULES.DIRECT.includes(STATEINDEX)) FD.append('DirectRule', true);
								
								if (LIMIT && LIMIT.includes(STATEINDEX)) FD.append('Limit', true);
								
								if (RULES.AUTOFILL && RULES.AUTOFILL.includes(STATEINDEX)) FD.append('AutoFill', true);
								
								if (Object.keys(AD).length && + STATEINDEX == 1) for (let d in AD) FD.append(d, AD[d]);
								
								let DIFF = [];
								
								let DIFFCONTENT = {};
								
								if (! NODATA) for (let i in DATACOMPLETED) {
									
									if (PROJECT.Difference != null && PROJECT.Difference.includes(i)) {
										
										DIFF.push(i);
										
										if (DATACOMPLETED[i][0] == 'select') DIFFCONTENT[i] = DATACOMPLETED[i][2];
										
									}
									
									FD.append(i, DATACOMPLETED[i][1]);
									
								}
								
								if (DIFF.length) {
									
									FD.append('Difference', JSON.stringify(DIFF));
									
									FD.append('DiffContent', JSON.stringify(DIFFCONTENT));
									
								}
										  
								DATACOMPLETED = {};
								
								PTDATA = {};
								
								TRACKER = [];
								
								el.classList.add('submit-preloader');
								
								SendRequest('Public/Php/Write.php', FD);
								
							}
							
						}
					
					}

				break;

				case 'Login':
				
					if (CANSEND) {
				
						let NIL = document.querySelector('#NotInList').checked;
							
						AUTHDATA = {'data': [], 'hash': (REGTYPE[4] == 'protect' ? md5(document.querySelector('#PasswordData').value.trim()) : '')};
						
						let AuthError = false;
						
						let LastADIndex;
						
						let AuthPool = document.querySelectorAll('[id*="Auth' + (NIL ? 'N' : '') + 'IL"]');
						
						[].forEach.call(AuthPool, function(x) { 
						
							if (x.value.trim() && ! /_/.test(x.value)) AUTHDATA.data.push(x.value.trim());
								
							else AuthError = true;
							
						});

						if (REGTYPE[1] == 'file' && REGTYPE[2] != null && ! AuthError) {
							
							if (! NIL) for (let d in AUTHDATA.data) {

								if (FILEDATA[d].fieldData.flat().length > 0 && ! FILEDATA[d].fieldData.flat().includes(AUTHDATA.data[d])) {
									
									AuthError = true;
									
									break;
									
								} else LastADIndex = FILEDATA[d].fieldData.flat().indexOf(AUTHDATA.data[d]);

							}
						
						} else if (! AUTHDATA.data.length) AuthError = true;

						if (REGTYPE[4] == 'protect' && ! AuthError) {
							
							if (NIL) {
								
								if (document.querySelector('#PasswordDataNotInList').value.trim() == '') AuthError = true;

							} else if (document.querySelector('#PasswordData').value.trim() == '') AuthError = true;
							
						}

						if (AuthError === false) {
							
							if (FILEDATA != null && FILEDATA.length > REGTYPE[2]) for (let i = REGTYPE[2]; i < FILEDATA.length; i ++) AD[FILEDATA[i].fieldName] = FILEDATA[i].fieldData.flat()[LastADIndex];
							
							FD.append('AuthData', JSON.stringify(AUTHDATA));
							
							FD.append('AuthType', REGTYPE[1]);
						
							FD.append('AuthCol', REGTYPE[2]);
							
							FD.append('AuthDef', REGTYPE[4]);
							
							FD.append('ProjectId', PROJECT.id);
							
							FD.append('Provisional', PROJECT.Provisional);
							
							CANSEND = false;
							
							SendRequest('Public/Php/Start.php', FD);
							
						} else al('AuthError');
					
					}

			    break;			   
			    
				case 'Back':

					if (CANSEND) {
						
						DATACOMPLETED = {};
						
						PTDATA = {};
						
						TRACKER = [];
						
						CANSEND = false;
						
						FD.append('ProjectId', PROJECT.id);
						
						FD.append('UserId', UID);
						
						if (JF && + STATEINDEX >= + JF) FD.append('JournalIndex', JOURNALINDEX);

						let IndexInHistory = HIO.indexOf(STATEINDEX);
						
						let StepBack = '';
						
						let HI = 0;
						
						let Last = true;
						
						if (IndexInHistory == -1) {
							
							for (HI in HIO) if (+ HIO[HI] > + STATEINDEX) {
								
								Last = false;
								
								break;
								
							}
							
							StepBack = HIO[HI - (Last ? 0 : 1)];
							
						} else StepBack = HIO[IndexInHistory - 1];
						
						FD.append('HistoryIndex', StepBack);
						
						el.classList.add('submit-preloader');
						
						SendRequest('Public/Php/Back.php', FD);
						
					}

				break;
				
				case 'Img':
				
					if (el.parentNode.className != 'img-container') {
				
						document.querySelector('#EContainer') && (document.querySelector('#EContainer').remove());
						
						PARENT = el.parentNode;
						
						let ImgContainer = new elem('div', {classname: 'img-container'});
						
						el.classList.add('big');
						
						ImgContainer.appendChild(el);

						document.querySelector('body').append(ImgContainer);
						
						if (el.dataset.track != null && TRACKER.includes(el.dataset.trackNumber)) {
							
							let TrackIndex = TRACKER.indexOf(el.dataset.trackNumber);
							
							TRACKER.splice(TrackIndex, 1);
							
						}
					
					} else {
						
						el.classList.remove('big');
						
						PARENT.appendChild(el);
						
						document.querySelector('.img-container').remove();

					}
				
				break;
				
				case 'BigImage':
				
					document.querySelector('.img-container').remove();
				
				break;

			}
		
		break;
		
	}
	
}

function GetProgress(x) {
    
    switch (PROJECT.Progress) {
        
        case 'N': return x + '/' + (PROJECT.Total - 1);
        
        break;
        
        case 'P': return Math.round(100 / ((PROJECT.Total - 1) / x)) + '%'; 

        break;
        
    }
    
}

function errorHandler(err){
    
    let msg = 'An error occured: ';
 
    switch (err.code) {
      
        case 1: case 8:
            
            msg += 'File or directory not found';
            
        break;
         
        case 4:
            
            msg += 'File or directory not readable';
            
        break;
         
        case 12:
        
            msg += 'File or directory already exists';
            
        break;
         
        case 11:
            
            msg += 'Invalid filetype';
            
        break;
         
        default:
        
            msg += 'Unknown Error ' + err.code;
            
        break;
 
    };
 
 console.log(msg);
 
};

function $_GET(key) {
	
    let p = window.location.search;
	
    p = p.match(new RegExp(key + '=([^&=]+)'));
	
    return p ? p[1] : false;
	
}