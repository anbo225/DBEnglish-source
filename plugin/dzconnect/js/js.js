function addSyncList(e, a) {
    var s = document.getElementById('syncList');
	var w = document.getElementById('synctowb');
    if (s.value) {
        s.value = s.value + ',';
    }
    if (s.value.indexOf(a) >= 0) {
        s.value = s.value.replace(a + ',', '');
        e.className = 'dz_m_link dz_m_no_' + a;
    } else {
        s.value += a + ',';
        e.className = 'dz_m_link dz_m_' + a;
    }
    s.value = s.value.replace(/,$/, '');
	if (s.value == '') {
		w.checked=false;
	} else {
		w.checked=true;
	}
}

function syncSelectAll(e) {
    var s = document.getElementById('syncList');
    var i = document.getElementById('syncIcon');
    var a = i.getElementsByTagName('a');
    if (e.checked == true) {
        s.value = '';
        for (var j = 0; j < a.length; j++) {
            a[j].className = 'dz_m_link dz_m_' + a[j].getAttribute('WB');
            s.value += a[j].getAttribute('WB') + ',';
        }
        s.value = s.value.replace(/,$/, '');
    } else {
        for (var j = 0; j < a.length; j++) {
            a[j].className = 'dz_m_link dz_m_no_' + a[j].getAttribute('WB');
        }
        s.value = '';
    }
}

function addSyncInput(){
	var mood=document.getElementById("mood_addform");
	var syncList=document.getElementById("syncList");
	var options = document.createElement("input");
	options.setAttribute("name", "syncList");
	options.setAttribute("id", "syncList");
	options.setAttribute("type", "hidden"); 
	options.setAttribute("value", syncList.value); 
	mood.appendChild(options); 
}