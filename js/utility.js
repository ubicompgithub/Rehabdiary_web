//a self defined function to get the size of an dictionary.
function lengthOf(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

//go to patient detail by userid
function toPatientDetail(uid){
   document.getElementById('input_uid').value = uid;
   document.getElementById('patient_detail_form').submit();
}

//from Date to 2013/09/24 format
function dateToString(_date, symbol){
   if(symbol == undefined) symbol = "/";
   return _date.getFullYear() + symbol + 
          ("0" + (_date.getMonth() +  1)).substr(-2) + symbol + 
          ("0" + _date.getDate()).substr(-2);
}

//a helper function when styling the table cell
function getStyle(width, pos){
   var _style = "";
   _style += 'width: ' + width + 'px; ';
   if(!pos)  _style += 'text-align: center; ';
   else      _style += 'text-align: ' + pos + ';';
   return {style: _style};
}

//a helper function to padding string from left with space. len <= 10
function padding(str, len){
   return ('        ' + str).slice(-len);
}

//go to certain_test detail by userid,timestamp,date
function toCertainTestDetail(uid,timestamp,d){
   document.getElementById('input_t_uid').value = uid;
   document.getElementById('input_timestamp').value = timestamp;
   document.getElementById('input_date').value = d;
   document.getElementById('certain_test_detail_form').submit();
}

function toPatientRecent(patientName){
      document.getElementById('input_recent_uid').value = patientName;
      document.getElementById('to_recent').submit();
}
function toPatientThink(patientName){
      document.getElementById('input_think_uid').value = patientName;
      document.getElementById('to_think').submit();
}
function toPatientAnalysis(patientName){
      document.getElementById('input_analysis_uid').value = patientName;
      document.getElementById('to_analysis').submit();
}
function toPatientDetail(patientName,item){
      document.getElementById('input_detail_uid').value = patientName;
      document.getElementById('item_num').value = item;
      document.getElementById('to_detail').submit();
}
