//FILTER INPUT CHARACTERS
var alphaNumericNcontrols = [ 
		8 /*backspace*/,9 /*tab*/,13 /*enter*/,16 /*shift*/,17 /*ctrl*/,18 /*alt*/,19 /*pause break*/,20 /*caps lock*/,32,33 /*page up*/,34 /*page down*/,
		35 /*end*/,36 /*home*/,37 /*left arrow*/,38 /*up arrow*/,39 /*right arrow*/,40 /*down arrow*/,44 /**/,45 /*insert*/,46 /*delete*/,145 /*scroll lock*/,
		47,48,49,50,51,52,53,54,55,56,57,58, //numbers 0-9
		64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,//big and small letters a-z
		/*96,*/97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,
		// numpad 0-9 numbers,
		173 /*_*/, 109 /*-*/, 27 /* esc key */, 110 /*.*/, 189 /* _ */, 190 /*.*/
		],
    illegal = {
		general: [ '!', '#', '$', '^', '&', '*', '(', ')', '"', '|' ],		
		general_shift: [16, 49, 51, 52, 53, 54, 55, 57],		
        reg: [ /*106/* * */, /*107/*+*/, /*111/* / */, 187/* = */, 192 /* grave accent */, 219 /*[*/, 220 /*\*/, 221 /*]*/, 222 /*'*/,      43, 39 ],
        shift: [ 56/*8*/,59/**/,188/*,*/, 220/*\*/,222/*'*/, 48 /* ) */, 190 /*>*/, 33, 34, 35, 36, 38, 39, 40, 94 ]
    }

jQuery(document).on("keypress", "input[type=text]", function(e) {
    var eKey = e.which ? e.which : e.keyCode;
	var characterInput = e.key;
	if(e.key == "" || e.key == "undefined"){
		characterInput = String.fromCharCode(eKey);
	}else{
		characterInput = e.key;
	}	
	//console.log(e);
	//console.log("char =" + characterInput + " code = " + eKey);		
	//if it is not in alphaNumericNcontrols array
    if (alphaNumericNcontrols.indexOf(eKey) === -1) {
		//console.log('zabranio 1: ' + eKey);
		return false;
	}
	//illegal characters
	if(characterInput && illegal.general.indexOf(characterInput) > -1){
		//console.log('zabranio 2: ' + eKey);
		return false;
	}
	//illegal characters and shift key
	if(e.shiftKey && illegal.general_shift.indexOf(eKey) > -1){
		//console.log('zabranio 3: ' + eKey);
		return false;
	}
	//illegal characters
    if (illegal.reg.indexOf(eKey) > -1) {
		//console.log('zabranio 4: ' + eKey);
		return false;
	}
	//illegal shift keys
    if (e.shiftKey && illegal.shift.indexOf(eKey) > -1) {
		//console.log('zabranio 5: ' + eKey);
		return false;
	}
	//console.log('Dozvolio: ' + eKey);
});