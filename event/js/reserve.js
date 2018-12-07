// 学年の最大数
var gradeMax = {}
// 現在チェックされている学生の数
var grade = {}
var gradeKey = ['D3', 'D2', 'D1', 'M2', 'M1', 'B4', 'B3', 'B2'];

function alla() {
	var t = document.getElementsByClassName('allClass')[0];
	if (t.checked) {
		for (var key in grade) {
			var tmp = document.getElementsByClassName(key+'Class')[0];
			if (tmp)
				tmp.checked = true;
		}
	} else {
		for (var key in grade) {
			var tmp = document.getElementsByClassName(key+'Class')[0];
			if (tmp)
				tmp.checked = false;
		}
	}
	for (var key in grade) {
		var tmp = document.getElementsByClassName(key+'Class')[0];
		if (tmp && key != 'all')
			allGroupCheck(key);
	}
}


function allGroupCheck(str) {
	var group = document.getElementsByClassName(str+'Class')[0];
	var t = document.getElementsByClassName(str);
	var bool;
	var add;
	if (group.checked) {
		bool = true;
		add = gradeMax[str];
	} else {
		bool = false;
		add = 0;
	}
	if (bool) {
		for (var i = 0; i < t.length; i++) {
			t[i].checked = true;
		}
	}else {
		for (var i = 0; i < t.length; i++) {
			t[i].checked = false;
		}
	}
	grade['all'] -= grade[str];
	grade[str] = add;
	grade['all'] += grade[str];
	if (gradeMax['all'] == grade['all'])
		document.getElementsByClassName('allClass')[0].checked = true;
	else 
		document.getElementsByClassName('allClass')[0].checked = false;
}

function check(str) {
	var t = document.getElementsByClassName(str);
	var sum = 0;
	for (var i = 0; i < t.length; i++) {
		if (t[i].checked)
			sum++;
	}
	grade['all'] += sum - grade[str];
	grade[str] = sum;

	if (gradeMax[str] == grade[str]) {
		document.getElementsByClassName(str + 'Class')[0].checked = true;
	} else {
		document.getElementsByClassName(str + 'Class')[0].checked = false;
	}
	if (grade['all'] == gradeMax['all']) 
		document.getElementsByClassName('allClass')[0].checked = true;
	else 
		document.getElementsByClassName('allClass')[0].checked = false;
}


window.onload = function() {
	grade['all'] = 0;
	var sum = 0;
	for (var i = 0; i < gradeKey.length; i++) {
		var num = document.getElementsByClassName(gradeKey[i]).length;
		sum += num;
		gradeMax[gradeKey[i]] = num;
		grade[gradeKey[i]] = 0;
	}
	gradeMax['all'] = sum;
}
