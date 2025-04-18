function ajax_request(data, url, getresponse) {
  if (window.XMLHttpRequest) {
    // code for IE7+, Firefox, Chrome, Opera, Safari
    xhr = new XMLHttpRequest();
  } else {
    // code for IE6, IE5
    xhr = new ActiveXObject("Microsoft.XMLHTTP");
  }
  xhr.open("POST", url, true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      //var json_parse = JSON.parse(xhr.responseText);
      //var get_response = json_parse.response;
      getresponse(xhr);
    }
  };
  xhr.send(data);
}
