function ajax_poll() {
  $.post(
    gbj.siteurl + "/ajax/ajax_service.php",
    { method: "instant_notifs" },
    function (data) {
      if (data.length == 0) {
        return;
      }
      var r = JSON.parse(data);
      if (r.notif.count > 0) {
        $(".notif-count").text(r.notif.count);
        $(".notif-count").show();
        var tone = new Audio(gbj.siteurl + "/assets/tone/plucky.mp3");
        tone.play();
        $(".user-notif-dropdown").prepend(r.notif.html);
      }

      if (r.msg.count > 0) {
        $(".msg-count").text(r.msg.count);
        $(".msg-count").show();
        var tone = new Audio(gbj.siteurl + "/assets/tone/plucky.mp3");
        tone.play();
        $(".user-message-dropdown").prepend(r.msg.html);
      }
    }
  );
}
/**
 * @param object dom_selectors
 */
function openMediaLibrary(dom_selectors) {
  $("#mediaUploader").modal();
  var attrs = {
    "data-media": dom_selectors.media_dom,
    "data-value": dom_selectors.value_dom,
  };
  $(".add-media").attr(attrs);
  $.get(
    gbj.siteurl + "/ajax/ajax-html.php",
    { request: "media-library-content" },
    function (data) {
      $(".media-library-explore").html(data);
    }
  );
}
$(function () {
  $.fn.add_tinymce = function (lang = "ar", dir = "rtl") {
    tinymce.init({
      selector: ".tinymce-area",
      language: lang,
      directionality: dir,
      width: "100%",
      height: "auto",
      autoresize_min_height: 150,
      //force_p_newlines : true,
      autoresize_max_height: 400,
      theme: "modern",
      plugins:
        "importcss paste autoresize autosave searchreplace autolink directionality visualblocks visualchars fullscreen link anchor toc insertdatetime advlist lists textcolor wordcount colorpicker textpattern",
      menubar: false,
      invalid_styles: "font-family font-size",
      paste_as_text: true,
      toolbar:
        "numlist bullist indent | forecolor | blockquote restoredraft rtl ltr | bold italic underline | link | alignleft aligncenter alignright alignjustify | mediaLibrary",
      inline_styles: true,
      importcss_append: true,
      content_css: [
        gbj.siteurl + "/assets/css/default.css",
        "//fonts.googleapis.com/earlyaccess/notonaskharabicui.css",
      ],
      init_instance_callback: function (editor) {
        editor.on("click", function (e) {
          console.log("Element clicked:", e.target.nodeName);
        });
      },
      setup: function (editor) {
        editor.addButton("mediaLibrary", {
          text: false,
          icon: "image",
          onclick: function () {
            openMediaLibrary({ media_dom: "tinymce", value_dom: "" });
          },
        });
        editor.on("init", function () {
          this.getDoc().body.style.fontSize = "23px";
        });
      },
    });
  };

  $.fn.upload_input = function (
    callback,
    progress_field = null,
    type = "",
    category = 1,
    file = 0
  ) {
    var input = $(this);
    var formdata = new FormData();
    file = file !== 0 ? file : input[0].files[0];
    formdata.append("file", file);
    formdata.append("method", "upload_ajax");
    formdata.append("type", type);
    formdata.append("file_category", category);

    var filename = file.name;
    var ext = filename.split(".").pop();
    var file_size = (file.size / (1024 * 1024)).toFixed(2); // megabytes
    var max_upload = gbj.site_max_upload; // megabytes

    if (gbj.ext_max_upload[ext] != "") {
      max_upload = gbj.ext_max_upload[ext];
    }

    if (parseFloat(max_upload) >= parseFloat(file_size)) {
      $.ajax({
        url: gbj.siteurl + "/ajax/ajax_service.php",
        type: "POST",
        data: formdata,
        xhr: function () {
          var xhr = new window.XMLHttpRequest();

          // Upload progress
          xhr.upload.addEventListener(
            "progress",
            function (evt) {
              if (evt.lengthComputable && progress_field) {
                var percentComplete = (evt.loaded / evt.total) * 100;
                progress_field.show();
                percentComplete = Math.round(percentComplete);
                progress_field.children(".progress-bar").addClass("active");
                progress_field
                  .children(".progress-bar")
                  .css("width", percentComplete + "%");
                progress_field.children(".progress-bar").text(percentComplete);
              }
            },
            false
          );

          return xhr;
        },
        processData: false,
        contentType: false,
        success: function (response) {
          response = JSON.parse(response);
          callback(response);
        },
        error: function (error) {
          console.log(error);
          swal({
            text: gbj.file_not_uploaded_text,
            icon: "error",
          });
          if(progress_field) {
            progress_field.hide();
          }
        },
      });
    } else {
      swal({
        text: gbj.big_file_text,
        icon: "error",
      });
    }
  };

  $.fn.ajax_req = function (callback, form_id, data) {
    $submit_btn = $(this);
    if (typeof data == "undefined") {
      if (typeof form_id != "undefined") {
        $form = $(form_id);
      } else {
        $form = $submit_btn.parents("form");
      }
      $form_serialize = $form.serializeArray();
    } else {
      $form_serialize = data;
    }

    $.ajax({
      url: gbj.siteurl + "/ajax/ajax_service.php",
      type: "POST",
      data: $form_serialize,
      success: function (response) {
        response = JSON.parse(response);
        callback(response);
        console.log(response);
      },
      error: function () {},
    });
  };

  $("body").tooltip({
    selector: "[data-toggle=tooltip]",
  });
  $('[data-toggle="popover"]').popover();

  $(document).on("click", ".is-invalid", function () {
    $(this).removeClass("is-invalid");
    $("#" + $(this).attr("id") + "_error_txt").text("");
  });
});
