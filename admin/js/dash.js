function imgPreview(input, preview_in, attr) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();

    reader.onload = function (e) {
      var create_img = document.createElement("img");
      create_img.src = e.target.result;
      //        if(attr) {
      $.each(attr, function (index, value) {
        create_img.setAttribute(value[0], value[1]);
      });
      //      }
      $(preview_in).html(create_img);
    };

    reader.readAsDataURL(input.files[0]);
  }
}

var dwidth = $(window).width();

$(window).on("resize", function (e) {
  if (dwidth < 992) {
    $("#panel_dashboard").addClass("no_sidebar");
  } else {
    $("#panel_dashboard").removeClass("no_sidebar");
  }
});

$(document).on("click", ".remove-parent", function (e) {
  var $this = $(this);
  $this.parent().remove();
  $(".menu-item-" + $this.data("type") + "-" + $this.data("id")).remove();
  e.preventDefault();
});

$(document).ready(function () {
  $(document).on("click", ".add-media", function () {
    var selected_media = $(".library-media.selected-media");
    $("#mediaUploader").modal("hide");
    var data_value = $(this).attr("data-value");
    var data_media = $(this).attr("data-media");
    $(data_value).val(selected_media.data("id"));
    var url = gbj.siteurl + "/file/" + selected_media.data("key") + "";
    if (data_media == "tinymce") {
      tinymce.activeEditor.insertContent('<img src="' + url + '"/>');
    } else if (data_media == "file") {
      $(".media-file-show").append(
        '<li class="file-area list-group-item d-flex align-items-center">' +
          '<i class="fas fa-file mr-2"></i>' +
          "<span>" +
          selected_media.attr("title") +
          "</span>" +
          '<span class="ml-auto remove-file" data-toggle="tooltip"><i class="fas fa-times"></i></span>' +
          '<input name="post_meta[books_ids][]" value="' +
          selected_media.data("id") +
          '" type="hidden"></li>'
      );
    } else {
      $(data_media).show();
      $(data_media).css({ "background-image": "url(" + url + ")" });
    }
  });

  var $loading_text =
    '<div class="text-center w-100"><i class="fas fa-spinner fa-spin"></i>' +
    gbj.loading +
    "</div>";

  $(document).on("click", ".library-media", function () {
    var $t = $(this);
    $(".uploader-image-preview").show();
    $(".prv-file-thumb").html(
      '<img src="' + $t.attr("src") + '" class="img-fluid"/>'
    );
    $(".prv-file-name").html($t.attr("title"));
    $(".prv-file-id").attr("data-id", $t.data("id"));
    $(".library-media").removeClass("selected-media");
    $t.addClass("selected-media");
  });

  $("#select-file-category,#select-file-source").on("change", function (e) {
    var file_category = $("#select-file-category").val();
    var source = $("#select-file-source").val();
    $(".media-library-explore").html($loading_text);
    $.get(
      gbj.siteurl + "/ajax/ajax-html.php",
      {
        request: "media-library-content",
        file_category: file_category,
        source: source,
      },
      function (data) {
        $(".media-library-explore").html(data);
      }
    );
  });

  var $form_media = $(".form-uploader");
  var droppedFiles = false;
  var $input = $form_media.find('input[type="file"]');
  $form_media.on(
    "drag dragstart dragend dragover dragenter dragleave drop",
    function (e) {
      e.preventDefault();
      e.stopPropagation();
    }
  );

  $form_media.on("dragover dragenter", function () {
    $form_media.addClass("on-drag");
  });

  $form_media.on("dragleave dragend drop", function () {
    $form_media.removeClass("on-drag");
  });

  $form_media.on("drop", function (e) {
    // when drag & drop is supported
    e.stopPropagation();
    droppedFiles = e.originalEvent.dataTransfer;
    $(".attachment_category").show();
  });

  $input.on("change", function (e) {
    // when drag & drop is NOT supported
    var $t = $(this);
    $(".attachment_category").show();
  });

  $(".attachment_category").on("change", function () {
    var file_category = $(this).val();
    $(".attachment_category").hide();
    if (droppedFiles) {
      $(droppedFiles).upload_input(
        function (r) {
          $("#form-media")[0].reset();
          $(".upload-progress").hide();
          $(".media-library-explore").prepend(
            '<div class="col-lg-2 col-md-4 col-sm-6 mb-4 position-relative">' +
              '<img src="' +
              r.file_url +
              '" class="img-fluid rounded library-media selected-media" alt="" data-key="' +
              r.file_key +
              '" data-id="' +
              r.file_id +
              '" data-original=""/>' +
              "</div>"
          );
          $("#media-library-tab").click();
        },
        $(".upload-progress"),
        "library_media",
        file_category
      );
    } else {
      $input.upload_input(
        function (r) {
          $("#form-media")[0].reset();
          $(".upload-progress").hide();
          $(".media-library-explore").prepend(
            '<div class="col-lg-2 col-md-4 col-sm-6 mb-4 position-relative">' +
              '<img src="' +
              r.file_url +
              '" class="img-fluid rounded library-media selected-media" alt="" data-key="' +
              r.file_key +
              '" data-id="' +
              r.file_id +
              '" data-original=""/>' +
              "</div>"
          );
          $("#media-library-tab").click();
        },
        $(".upload-progress"),
        "library_media",
        file_category
      );
    }
  });

  $("#select_type_ext").on("change", function (e) {
    var ext = $(this).val();
    $(".custom-ext-size").hide();
    $(
      '.custom-ext-size[name="general_settings[ext_max_upload][' + ext + ']"]'
    ).show();
  });

  $("#pick_action").on("change", function (e) {
    var $this = $(this);
    if ($this.val() == "move") {
      $(".pick-cat-tomove").show();
    } else {
      $(".pick-cat-tomove").hide();
    }
  });

  var $loadModal = $("#loadModal");
  var $loadModal_content = $loadModal.html();
  $loadModal.on("hidden.bs.modal", function (e) {
    $(this).html($loadModal_content);
  });
  $(".open-all-notifs").click(function (e) {
    var $t = $(this);
    $("#loadModal").modal();
    $.get(
      "../ajax/ajax-html.php",
      { request: "notification-modal", post_id: $t.data("post") },
      function (data) {
        $("#loadModal").html(data);
      }
    );
    e.preventDefault();
  });
  if ($(window).width() < 992) {
    $("#panel_dashboard").addClass("no_sidebar");
  }
  $(".add-new-post").click(function () {
    var $t = $(this);
    if ($t.hasClass("login-modal")) {
      $("#signinModal").modal("show");
      return;
    }
    if ($("#addpostModal").length == 0) {
      $.get(
        gbj.siteurl + "/ajax/ajax-html.php?request=add-post-modal",
        function (data) {
          $("body").prepend(data);
        }
      );
    } else {
      $("#addpostModal").modal();
    }
  });

  var audioElement = document.createElement("audio");
  audioElement.setAttribute("src", gbj.siteurl + "/assets/tone/plucky.mp3");

  $(".usradd-btn-tbar").click(function () {
    $("#loader").show();
    if ($("#add_new_content").length == 0) {
      $.get(
        "html_loader.php?token_request=" +
          global_token_request +
          "&path=add_content",
        function (data) {
          $("body").prepend(data);
          $("#loader").hide();
        }
      );
    } else {
      $("#add_new_content").show();
    }
  });
  $(".delete-btn").click(function () {
    if (confirm("هل متأكد أنك تريد إكمال العملية ؟")) {
      return true;
    } else {
      return false;
    }
  });
  
  // $(".on_change_submit").on("change", function () {
  //   let $form = $("#form_filter");

  //   // Check if form has the class 'fetch'
  //   if ($form.hasClass("fetch")) {
  //       // Modify the form action by appending 'action=fetch' to the URL
  //       let url = new URL($form.attr("action") || window.location.href);
  //       url.searchParams.append("action", "fetch");

  //       // Set the modified URL back to the form's action attribute
  //       $form.attr("action", url.toString());
  //   }

  //   // Submit the form
  //   $form.submit();
  // });

  function form_submit_with_get_req(form) {
    let formdata = new FormData(form.get(0));
    let url = form.attr('action');
    let hasGetRequest = false;
    if(url.match(/\?/)) {
      hasGetRequest = true;
    }
    let counter = 0;
    for(let key of formdata.keys()) {
      let getMark = hasGetRequest === true ? "&" : (counter == 0 ? "?" : "&");
      url = `${url}${getMark}${key}=${formdata.get(key)}`
      counter++;
    }
    // Update the form's action attribute with the new URL
    window.location.href = url;
  }

  $("#form_filter2").unbind().on("submit", function (e) {
    e.preventDefault();
    let form = $(this);
    form_submit_with_get_req(form);
  });

  $(".on_change_submit").on("change", function (e) {
    e.preventDefault();
    let form = $(this).parents('form');
    if(form.is(`#form_filter2`)) {
      form_submit_with_get_req(form);
    } else {
      form.get(0).submit();
    }
  });
  
  $(".toggle-sidebar").click(function () {
    if ($("#panel_dashboard").hasClass("no_sidebar")) {
      $("#panel_dashboard").removeClass("no_sidebar");
    } else {
      $("#panel_dashboard").addClass("no_sidebar");
    }
  });
  $(".select-checkbox-all").click(function () {
    if ($(this).is(":checked")) {
      $(".select-checkbox").prop("checked", true);
      $(".select-checkbox").each(function (i, val) {
        $(".checkbox-content-" + val.value).remove();
        $("#form_actions, #action-form").append(
          '<input type="hidden" name="content_id[]" class="checkbox-content-' +
            val.value +
            '" value="' +
            val.value +
            '"/>'
        );
      });
    } else {
      $(".select-checkbox").prop("checked", false);
      $(".select-checkbox").each(function (i, val) {
        $(".checkbox-content-" + val.value).remove();
      });
    }
  });
  $(".select-checkbox").click(function () {
    $(".select-checkbox-all").prop("checked", false);
    if ($(this).is(":checked")) {
      $("#form_actions, #action-form").append(
        '<input type="hidden" name="content_id[]" class="checkbox-content-' +
          $(this).val() +
          '" value="' +
          $(this).val() +
          '"/>'
      );
    } else {
      $(".checkbox-content-" + $(this).val()).remove();
    }
  });
  $(".preview-st-btn").on("click", function () {
    var post_id = $(this).data("id");
    $(".post_quick_preview").show();
    $.get(
      "html_loader.php?token_request=" +
        global_token_request +
        "&path=quick_preview&post_id=" +
        post_id +
        "",
      function (data) {
        data = JSON.parse(data);
        $(".quick_preview_image").html(data.post_thumb);
        $(".quick_preview_title").html("<h1>" + data.post_title + "</h1>");
        $(".quick_preview_content").html(data.post_content);
      }
    );
  });

  var target_inp = "";

  $(".upload-btn").on("click", function (e) {
    $(".file-inp").click();
    target_inp = $(this).data("input");
    e.preventDefault();
  });

  $(".file-inp").on("change", function (e) {
    var category_id = $(".attachment_category_class").val();

    var t = $(this);
    var req = "admin_attachment";
    if (target_inp == "#media_library") {
      req = "site_images";
    }
    t.upload_input(
      function (r) {
        if (target_inp == "#media_library") {
          location.reload();
        } else {
          $(target_inp).val(r.file_id);
          $(target_inp + "_prv").attr("src", r.file_url);
        }
      },
      null,
      req,
      category_id
    );
  });
});

$(".upload-mo-file").on("click", function (e) {
  e.preventDefault();
});

$(document).on("click", ".close-btn", function () {
  var element_animation_effect;
  var data_element_close = $(this).attr("data-element-close");
  var data_delay_close = $(this).attr("data-delay-close");
  var animation_name = $(this).attr("data-animation");
  var data_close_add_animation = $(this).attr("data-close-add-animation");
  element_animation_effect = data_element_close;
  if (data_close_add_animation) {
    element_animation_effect = data_close_add_animation;
  }
  $("." + element_animation_effect + "").addClass(
    "animated " + animation_name + ""
  );
  setTimeout(function () {
    $("." + element_animation_effect + "").removeClass(
      "animated " + animation_name + ""
    );
    $("." + data_element_close + "").hide();
  }, data_delay_close);
});
$(document).on("click", ".show-child", function () {
  $(".show-child").removeClass("active-dash-m");
  if ($(this).hasClass("active-dash-m")) {
    //
  } else {
    $(this).addClass("active-dash-m");
  }
  // $(".show-child").children("ul")
  // $(this).children("ul").toggle(800,function() {

  //});
});

$(document).on("click", ".saveData", function (e) {
  tinymce.triggerSave();
  var this_btn = $(this);
  this_btn.ajax_req(function (r) {
    if (r.success === true) {
      swal({
        text: r.msg,
        icon: "success",
      }).then(function() {
        if(window.location.href.match(/authors\?section/)) {
          window.location = '/admin/dashboard/authors';
        } else if(window.location.href.match(/boots\?action=(add_comment|edit_comment)/)) {
          window.location = '/admin/dashboard/boots?action=comments';
        } else if(window.location.href.match(/boots\?action=(add|edit\&boot_id\=\d+)$/)) {
          window.location = '/admin/dashboard/boots';
        }
      });
    } else {
      swal({
        text: r.msg,
        icon: "warning",
      });
      $(".error-inp-txt").text("");
      if (typeof r.input_errors == "object") {
        $.each(r.input_errors, function (i, v) {
          console.log(v.error);
          $(v.selector + "_error").text(v.error);
        });
      }
    }
  });
  e.preventDefault();
});

$(".cv-badge-request").on("change", function (e) {
  var $this = $(this);
  var data = {
    method: $this.data("method"),
    user_id: $this.data("user"),
    request: $this.val(),
  };
  $this.ajax_req(
    function (r) {
      if (r.success === true) {
        swal({
          text: r.msg,
          icon: "success",
        });
      } else {
        swal({
          text: r.msg,
          icon: "warning",
        });
      }
    },
    null,
    data
  );
});

$(document).on("click", "#confirm-box", function () {
  $("#alert-popup-models").remove();
});

$(document).on("hidden.bs.modal", "#addpostModal", function () {
  $(".choose-post-in").show();
  $(".choose-post-type").hide();
  $(".radio-select-post-type .custom-control-input").prop("checked", false);
});

$(document).on("click", ".open-url", function () {
  var data_url = $(this).attr("data-url");
  window.location.href = data_url;
});
$(document).on("click", ".updateData", function () {
  tinymce.triggerSave();
  var this_btn = $(this);
  this_btn.attr({ disabled: true, "data-load": true });
  var data_method = this_btn.data("method");
  var data_id = this_btn.data("id");
  var data = {
    id: data_id,
    method: data_method,
  };
  if(this_btn.is(`[data-stat]`)) {
    data['stat'] = this_btn.data('stat');
  }
  this_btn.ajax_req(
    function (r) {
      if (r.success === true) {
        if (
          data_method == "un_lock_post_ajax" ||
          data_method == "un_lock_user_ajax" ||
          data_method == "un_lock_ad_ajax" ||
          data_method == "un_lock_badge_ajax" ||
          data_method == "un_lock_page_ajax"
        ) {
          if (this_btn.children("i").hasClass("fa-lock-open")) {
            this_btn
              .children("i")
              .removeClass("fa-lock-open")
              .addClass("fa-lock");
          } else {
            this_btn
              .children("i")
              .removeClass("fa-lock")
              .addClass("fa-lock-open");
          }
        } else if (data_method == "un_verfiy_users") {
          if (this_btn.children("i").hasClass("far")) {
            this_btn.children("i").removeClass("far").addClass("fas");
          } else {
            this_btn.children("i").removeClass("fas").addClass("far");
          }
        } else if (data_method == "merge_to_un_trusted") {
          if (this_btn.children("i").hasClass("far")) {
            this_btn.children("i").removeClass("far").addClass("fas");
          } else {
            this_btn.children("i").removeClass("fas").addClass("far");
          }
        } else if (data_method == "authorize_share") {

          if (this_btn.children("i").hasClass("far")) {
            this_btn.css({
              'background-color': 'green',
              'color': 'white'
            });
            this_btn.children("i").removeClass("far").addClass("fas").attr('title', 'إلغاء سحب حق النشر');;
          } else {
            this_btn.css({
              'background-color': '#989797',
              'color': '#c5c5c5'
            });
            this_btn.children("i").removeClass("fas").addClass("far").attr('title', 'سحب حق النشر');
          }
          
        } else if (data_method == "category_visibility") {
          if (this_btn.children("i").hasClass("fa-eye-slash")) {
            this_btn
              .children("i")
              .removeClass("fa-eye-slash")
              .addClass("fa-eye");
          } else {
            this_btn
              .children("i")
              .removeClass("fa-eye")
              .addClass("fa-eye-slash");
          }
        }
      } else {
      }
      this_btn.attr({ disabled: false, "data-load": false });
    },
    null,
    data
  );
});

$(document).on("click", ".refresh-btn", function () {
  $(".model-change-role").show();
  var user_id = $(this).data("user");
  $(".user_role_id").val(user_id);
});

$(document).on("change", ".check-all-multi", function (e) {
  $(".action-selected-inps").remove();
  var $t = $(this);
  if ($t.prop("checked")) {
    $(".check-box-action").each(function () {
      $("#action-form").append(
        '<input type="hidden" name="id[]" class="action-selected-inps" value="' +
          $(this).data("id") +
          '"/>'
      );
    });
  } else {
    $(".action-selected-inps").remove();
  }
});

$(document).on("change", ".check-box-action", function (e) {
  $(".check-all-multi").prop("checked", false);
  var $t = $(this);
  if ($t.prop("checked")) {
    if($t.is(`[data-stat]`)) {
      $("#action-form").append(
        '<input type="hidden" name="stats[]" class="action-selected-inps" value="' +
          $t.data("stat") +
          '"/>'
      );
    }
    $("#action-form").append(
      '<input type="hidden" name="id[]" class="action-selected-inps" value="' +
        $t.data("id") +
        '"/>'
    );
  } else {
    $('.action-selected-inps[value="' + $t.data("id") + '"]').remove();
  }
});

$(".submit-action").on("click", function (e) {
  var $t = $(this);
  $t.attr({ disabled: true, "data-loading": true });
  $t.ajax_req(function (r) {
    if (r.success == true) {
      swal({
        text: r.msg,
        icon: "success",
        buttons: {
          حسنا: true,
        },
      }).then((clicked) => {
        if (clicked) {
          location.reload();
        }
      });
    } else {
      swal({
        text: r.msg,
        icon: "error",
      });
    }
    $t.attr({ disabled: false, "data-loading": false });
  });
  e.preventDefault();
});

$(document).on("click", ".change_role", function () {
  var $t = $(this);
  var data = {
    method: "change_role",
    role: $(".new_role").val(),
    user_id: $(".user_role_id").val(),
  };
  $t.ajax_req(
    function (r) {
      if (r.success == true) {
        swal({
          text: "تم تغيير الرتبة",
          icon: "success",
          buttons: {
            حسنا: true,
          },
        }).then((clicked) => {
          if (clicked) {
            location.reload();
          }
        });
      } else {
        swal("", r.msg);
      }
    },
    null,
    data
  );
});

$(document).on("click", ".send_msg_btn", function () {
  $(".model-send-msg").show();
  var user_id = $(this).data("user");
  $(".user_msg_id").val(user_id);
});

$(document).on("click", ".m-toggle-btn", function () {
  var element_animation_effect;
  var data_element_show = $(this).attr("data-element-show");
  var data_delay_show = $(this).attr("data-delay-show");
  var animation_name = $(this).attr("data-animation");
  var data_show_add_animation = $(this).attr("data-show-add-animation");
  element_animation_effect = data_element_show;
  if (data_show_add_animation) {
    element_animation_effect = data_show_add_animation;
  }
  $("." + element_animation_effect + "").addClass(
    "animated " + animation_name + ""
  );
  $("." + data_element_show + "").toggle();
  setTimeout(function () {
    $("." + element_animation_effect + "").removeClass(
      "animated " + animation_name + ""
    );
  }, data_delay_show);
});
$(document).on("click", ".cancel-model", function (e) {
  $(".model-main").hide();
  e.preventDefault();
});

$(document).on("click", ".send-alert", function (e) {
  var this_btn = $(this);
  this_btn.ajax_req(function (r) {
    if (r.success == true) {
      swal({
        text: "تم إرسال الرسالة بنجاح.",
        icon: "success",
        buttons: {
          حسنا: true,
        },
      }).then((clicked) => {
        if (clicked) {
          location.reload();
        }
      });
    } else {
      swal("", r.msg, "error");
    }
  }, "#send_message_form");
  e.preventDefault();
});
