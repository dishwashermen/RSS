let dialogData = {
	
	'AuthError': {
		
		DialogType: 'confirm',
		
		TrueButtonText: '',
				
		FalseButtonText: '',
				
		QuestionText: 'Не заполнены данные для авторизации'
		
	},
	
	'LoginError': {
		
		DialogType: 'confirm',
		
		TrueButtonText: '',
				
		FalseButtonText: '',
				
		QuestionText: 'Запрос на вход отклонён'
		
	},
	
	'SinglePrompt': {
		
		DialogType: 'prompt',
		
		TrueButtonText: 'Записать',
				
		FalseButtonText: 'Отмена',
				
		TitleText: 'Впишите ответ'
		
	}
	
}

class ConfirmDialog {

	constructor({

		TitleText,
	
		QuestionText, 
		
		TrueButtonText, 
		
		FalseButtonText, 
		
		ExtButtonText, 
		
		Parent,
		
		DialogType
		
	}) {
		
		this.DialogType = DialogType || '';
		
		this.TitleText = TitleText || '';
	  
		this.QuestionText = QuestionText || '';
		
		this.TrueButtonText = TrueButtonText || 'ОК';
		
		this.FalseButtonText = FalseButtonText || '';
		
		this.ExtButtonText = ExtButtonText || '';
		
		this.Parent = Parent || document.body;

		this.Dialog = undefined;
		
		this.TrueButton = undefined;
		
		this.FalseButton = undefined;

		if (this.DialogType == 'confirm') this._createConfirmDialog();
		
		if (this.DialogType == 'prompt') this._createPromptDialog();
		
		this._appendDialog();
	
	}

	confirm() {
	  
		return new Promise((resolve, reject) => {
			
			const somethingWentWrongUponCreation = ! this.Dialog || ! this.TrueButton || ! this.FalseButton;
		  
			if (somethingWentWrongUponCreation) {
			  
				reject('Someting went wrong when creating the modal');
			
				return;
			
			}

			this.Dialog.showModal();

			this.TrueButton.addEventListener("click", () => {
			  
				resolve(this.promptInput);
			
				this._destroy();
			
			});

			this.FalseButton.addEventListener("click", () => {
			  
				resolve(false);
			
				this._destroy();
			
			});
			
			this.extButton.addEventListener("click", () => {
			  
				resolve('ext');
			
				this._destroy();
			
			});
		  
		});
	
	}

	_createConfirmDialog() {
	  
		this.Dialog = elem('Dialog', {classname: 'confirm-dialog'});

		this.question = elem('div', {classname: 'confirm-dialog-question', innerhtml: this.QuestionText});
		
		this.Dialog.appendChild(this.question);

		const buttonGroup = elem('div', {classname: 'confirm-dialog-button-group'});
		
		this.Dialog.appendChild(buttonGroup);
		
		this.extButton = elem('button', {classname: 'confirm-dialog-button confirm-dialog-button--ext', type: 'button', textcontent: this.ExtButtonText});
		
		buttonGroup.appendChild(this.extButton);
		
		this.divider1 = elem('span', {classname: 'Dialog-divider'});
		
		buttonGroup.appendChild(this.divider1);

		this.FalseButton = elem('button', {classname: 'confirm-dialog-button confirm-dialog-button--false', type: 'button', textcontent: this.FalseButtonText});
		
		buttonGroup.appendChild(this.FalseButton);
		
		this.divider2 = elem('span', {classname: 'Dialog-divider'});
		
		buttonGroup.appendChild(this.divider2);
		
		this.TrueButton = elem('button', {classname: 'confirm-dialog-button confirm-dialog-button--true', type: 'button', textcontent: this.TrueButtonText});
		
		buttonGroup.appendChild(this.TrueButton);

	}
	
	_createPromptDialog() {
	  
		this.Dialog = elem('Dialog', {classname: 'prompt-dialog'});

		this.title = elem('div', {classname: 'prompt-dialog-question', innerhtml: this.TitleText});
		
		this.Dialog.appendChild(this.title);
		
		this.promptInput = elem('textarea', {classname: 'prompt-dialog-textarea'});
		
		this.Dialog.appendChild(this.promptInput);

		const buttonGroup = elem('div', {classname: 'prompt-dialog-button-group'});
		
		this.Dialog.appendChild(buttonGroup);
		
		this.extButton = elem('button', {classname: 'prompt-dialog-button prompt-dialog-button--ext', type: 'button', textcontent: this.ExtButtonText});
		
		buttonGroup.appendChild(this.extButton);
		
		this.divider1 = elem('span', {classname: 'dialog-divider'});
		
		buttonGroup.appendChild(this.divider1);

		this.FalseButton = elem('button', {classname: 'prompt-dialog-button prompt-dialog-button--false', type: 'button', textcontent: this.FalseButtonText});
		
		buttonGroup.appendChild(this.FalseButton);
		
		this.divider2 = elem('span', {classname: 'dialog-divider'});
		
		buttonGroup.appendChild(this.divider2);
		
		this.TrueButton = elem('button', {classname: 'prompt-dialog-button prompt-dialog-button--true', type: 'button', textcontent: this.TrueButtonText});
		
		buttonGroup.appendChild(this.TrueButton);
		
		this.promptInput.focus();

	}

	_appendDialog() {
		  
		this.Parent.appendChild(this.Dialog);
		
	}

	_destroy() {
	  
		this.Parent.removeChild(this.Dialog);
		
		delete this;
	
	}
  
}

async function al(e, data = false) {
	
	let id = typeof(e.target) != 'undefined' ? e.target.id : e;
	
	switch (id) {

		case 'LoginError':
		
			const LoginErrorDialog = new ConfirmDialog(dialogData[id]);
			
			await LoginErrorDialog.confirm();
		
		break;
		
		case 'AuthError':
		
			const AuthErrorDialog = new ConfirmDialog(dialogData[id]);
			
			await AuthErrorDialog.confirm();
		
		break;
		
		case 'SinglePrompt':
		
			const SinglePromptDialog = new ConfirmDialog(dialogData[id]);
			
			PROMPTRESULT = await SinglePromptDialog.confirm();
		
		break;
		
		case 'ModalDialog':
		
			const ModalDialog = new ConfirmDialog({DialogType: 'confirm', 'QuestionText': data});
			
			await ModalDialog.confirm();
		
		break;

	}
	
}

