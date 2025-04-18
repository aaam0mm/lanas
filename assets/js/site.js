$(window).on("load", function () {
  if (parseInt($(".notif-count").text()) > 0) {
    $(".notif-count").show();
  }
});

$(document).ready(function () {
  const observer = lozad(".lazy-load", {
    loaded: function (el) {
      // Custom implementation on a loaded element
      el.classList.add("loaded");
    },
  });
  observer.observe();

  $("#emojoComment1").emojioneArea({
    standalone: true,
    filters: {
      recent: false, // disable recent
    },
    attributes: {
      dir: "rtl",
    },
    events: {
      keyup: function (editor, event) {
        if (event.key === "Enter" && !event.shiftKey) {
          event.preventDefault();
          $("#emojoComment1").parents('form').find('.add-comment').click();
        }
      }
    }
  });

  var $loading_text =
    '<div class="text-center w-100"><i class="fas fa-spinner fa-spin"></i>' +
    gbj.loading +
    "</div>";

  $("#history_calendar_select").on("change", function (e) {
    $("#match-calender").val($(this).val());
    $("#filter-form").submit();
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

  $(document).on("click", ".open-media-books", function () {
    var file_category = $("#select-file-category").val();
    var source = $("#select-file-source").val();
    $(".media-library-explore").html($loading_text);
    $.get(
      gbj.siteurl + "/ajax/ajax-html.php",
      {
        request: "media-library-content",
        file_category: file_category,
        source: source,
        mime_type: "pdf",
      },
      function (data) {
        $(".media-library-explore").html(data);
      }
    );
  });

  $(".order-by-button").on("click", function (e) {
    var $this = $(this);
    var order = $this.data("order");
    if (order == "desc") {
      order = "asc";
    } else if (order == "asc") {
      order = "desc";
    }
    $('[name="order_by"]').val(order);
    $("#filter-form").submit();
    e.preventDefault();
  });

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

  var resize = new Array(".resizeable p", ".resizeable span");
  resize = resize.join(",");

  //resets the font size when "reset" is clicked
  var resetFont = $(resize).css("font-size");
  $(".reset").click(function () {
    $(resize).css("font-size", resetFont);
  });

  //increases font size when "+" is clicked
  $(".plus-font-size").click(function () {
    var originalFontSize = $(resize).css("font-size");
    var originalFontNumber = parseFloat(originalFontSize, 10);
    var newFontSize = originalFontNumber * 1.2;
    $(resize).css("font-size", newFontSize);
    return false;
  });

  //decrease font size when "-" is clicked

  $(".minus-font-size").click(function () {
    var originalFontSize = $(resize).css("font-size");
    var originalFontNumber = parseFloat(originalFontSize, 10);
    var newFontSize = originalFontNumber * 0.8;
    $(resize).css("font-size", newFontSize);
    return false;
  });

  var $loadModal = $("#loadModal");
  var $loadModal_content = $loadModal.html();
  $loadModal.on("hidden.bs.modal", function (e) {
    $(this).html($loadModal_content);
  });

  $(".to-top").on("click", function (e) {
    $("html, body").animate(
      {
        scrollTop: 0,
      },
      "slow"
    );
  });

  $(".taxos-explore-text").on("click", function (e) {
    e.preventDefault();
  });

  $(".tool_tip")
    .tooltip({
      trigger: "manual",
    })
    .tooltip("show");

  $(".delete-conversation-js").on("click", function (e) {
    var $t = $(this);
    var data = {
      method: "delete_conversation",
      id: $t.data("id"),
    };
    $t.ajax_req(
      function (r) {
        if (r.success === true) {
          window.location.href = "messages.php";
        } else {
          swal({
            text: r.msg,
            icon: "error",
            button: gbj.ok_text,
          });
        }
      },
      null,
      data
    );
    e.preventDefault();
  });

  $(".close-alert-group").on("click", function () {
    var $t = $(this);
    var id = $t.data("id");
    var data = {
      method: "close_notif",
      id: id,
    };
    $t.ajax_req(
      function (r) {
        $t.closest("div").remove();
      },
      null,
      data
    );
  });

  $(".print-cv").on("click", function (e) {
    PrintElem($(this).attr("href"));
    e.preventDefault();
  });

  $(".explore-search-results").on("click", function (e) {
    $("#searchForm").submit();
    e.preventDefault();
  });

  // home page js
  var animation_in;
  var animation_name;
  $(".hover-animate").hover(
    function () {
      animation_in = $(this).data("animation-in");
      animation_name = $(this).data("animation-name");
      $(animation_in).addClass("animated " + animation_name);
    },
    function () {
      $(animation_in).removeClass("animated " + animation_name);
    }
  );

  $(".click-animate").click(function () {
    animation_in = $(this).data("animation-in");
    animation_name = $(this).data("animation-name");
    $(animation_in).addClass("animated " + animation_name);
    setTimeout(function () {
      $(animation_in).removeClass("animated " + animation_name);
    }, 1500);
  });

  $(".remove-jumbotron-home").click(function () {
    $(".jumbotron-home").remove();
  });

  $(".notif-open-btn").click(function () {
    $(".notif-count").text(0);
    $(".notif-count").hide();
    $.post(gbj.siteurl + "/ajax/ajax_service.php", {
      method: "open_notifs",
    });
  });

  $(".msg-open-btn").click(function () {
    $(".msg-count").text(0);
    $(".msg-count").hide();
    $.post(gbj.siteurl + "/ajax/ajax_service.php", {
      method: "open_msgs",
    });
  });

  $(".home-page-video-thumb").click(function () {
    var $this = $(this);
    $("#homeEmbedvideo").modal("show");
    $.get(
      gbj.siteurl + "/ajax/ajax-html.php",
      {
        request: "homeVideo",
        url: $this.data("url"),
      },
      function (data) {
        $("#homeEmbedvideo .modal-body").html(data);
      }
    );
  });

  $("#history_calendar").on("change", function () {
    var $t = $(this);
    var calendar = $t.val();
    $("#history_month").html("<option disbaled>" + gbj.loading + "</option>");
    $.get(
      gbj.siteurl + "/ajax/ajax-html.php",
      {
        request: "history-months",
        calendar: calendar,
      },
      function (data) {
        $("#history_month").html(data);
      }
    );
  });

  $(document).on("click", ".share-btn", function (e) {
    var $t = $(this);
    var url = $t.data("url");
    window.open(
      url,
      "_blank",
      "toolbar=yes,scrollbars=yes,resizable=yes,top=500,left=500,width=600,height=400"
    );
    e.preventDefault();
  });

  $(document).on("click", ".toggle-history-btn", function (e) {
    $(".history-tab-shown")
      .removeClass("history-tab-shown")
      .addClass("history-tab-hidden");
    var tab = $(this).data("tab");
    $(tab).removeClass("history-tab-hidden").addClass("history-tab-shown");
    e.preventDefault();
  });

  $(document).on("change", ".check-all-multi", function (e) {
    $(".action-selected-inps").remove();
    var $t = $(this);
    if ($t.prop("checked")) {
      $(".check-box-action").each(function () {
        $("#action-form,#move-form").append(
          '<input type="hidden" name="id[]" class="action-selected-inps" value="' +
            $(this).data("id") +
            '"/>'
        );
      });
    } else {
      $(".action-selected-inps").remove();
    }
  });

  $(".submit-action").on("click", function (e) {
    var $t = $(this);
    $t.attr({
      disabled: true,
      "data-loading": true,
    });
    $t.ajax_req(function (r) {
      if (r.success == true) {
        swal({
          title: r.msg,
          icon: "success",
          button: gbj.ok_text,
        }).then((value) => {
          location.reload();
        });
      } else {
        swal({
          text: r.msg,
          icon: "error",
          button: gbj.ok_text,
        });
      }
      $t.attr({
        disabled: false,
        "data-loading": false,
      });
    });
    e.preventDefault();
  });

  $(document).on("change", ".check-box-action", function (e) {
    $(".check-all-multi").prop("checked", false);
    var $t = $(this);
    if ($t.prop("checked")) {
      $("#action-form,#move-form").append(
        '<input type="hidden" name="id[]" class="action-selected-inps" value="' +
          $t.data("id") +
          '"/>'
      );
    } else {
      $('.action-selected-inps[value="' + $t.data("id") + '"]').remove();
    }
  });

  var emojiForm = 1;

  $(document).on("click", ".add-reply", function (e) {
    emojiForm++;
    var comment_id = $(this).data("id");
    var $reply_form = $("#comment-form").clone();

    $reply_form.find("#emojoComment1").attr("id", "emojoComment" + emojiForm);
    $reply_form.find(".emojionearea").remove();

    $reply_form.append(
      '<input type="hidden" name="reply_to" value="' + comment_id + '"/>'
    );
    $reply_form
      .find(".comment-btns")
      .append(
        '<button class="btn btn-light ml-2 cancel-reply-comment">إلغاء</button>'
      );

    $(".comment-childs-" + comment_id)
      .find(".reply-form")
      .html("");
    $(".comment-childs-" + comment_id)
      .find(".reply-form")
      .html($reply_form);

    $("#emojoComment" + emojiForm).emojioneArea({
      filters: {
        recent: false, // disable recent
      },
      attributes: {
        dir: "rtl",
      },
    });

    e.preventDefault();
  });

  $(document).on("click", ".load-more", function (e) {
    var $t = $(this);
    $t.attr({
      disabled: true,
      "data-loading": true,
    });
    var paged = $t.data("paged") + 1;
    var paged_max = $t.data("paged-max");
    var request = $t.data("request");
    request.paged = paged;
    var darea = $t.data("area");
    if (paged <= paged_max) {
      $.get(gbj.siteurl + "/ajax/ajax-html.php", request, function (data) {
        if ($(darea + " #load_more_area").length !== 0) {
          $(data).insertBefore("#load_more_area");
        } else {
          $(darea).append(data);
        }
        $('[data-toggle="popover"]').popover();
        $t.data("paged", paged);
        if (paged == paged_max) {
          $t.remove();
        }
        $t.attr({
          disabled: false,
          "data-loading": false,
        });
      });
    } else {
      $t.remove();
    }
    e.preventDefault();
  });

  $(".open-all-notifs").click(function (e) {
    var $t = $(this);
    $("#loadModal").modal("show");
    $.get(
      gbj.siteurl + "/ajax/ajax-html.php",
      {
        request: "notification-modal",
        post_id: $t.data("post"),
      },
      function (data) {
        $("#loadModal").html(data);
      }
    );
    e.preventDefault();
  });

  $(document).on("click", ".open-complain-form", function (e) {
    var $t = $(this);
    var comment_id = $t.data("comment");
    var data = {
      request: "complain-form",
      post_id: $t.data("post"),
    };
    if (comment_id) {
      data.comment_id = comment_id;
    }
    $("#loadModal").modal("show");
    $.get(gbj.siteurl + "/ajax/ajax-html.php", data, function (data) {
      $("#loadModal").html(data);
    });
    e.preventDefault();
  });

  $(".navigate-history").on("click", function (e) {
    var $this = $(this);
    var history = $this.data("history");
    var data = {
      request: "history_navigate",
      v: history,
    };
    $.get(gbj.siteurl + "/ajax/ajax-html.php", data, function (data) {
      $(".history-posts-load").html(data);
    });
  });

  $(document).on("click", ".show-replies", function (e) {
    var $this = $(this);
    var id = $this.data("id");
    $.get(
      gbj.siteurl + "/ajax/ajax-html.php",
      {
        request: "replies",
        comment_id: id,
        per_page: 5,
      },
      function (data) {
        $(".comment-childs-" + id + " .users-replies").html(data);
      }
    );
    e.preventDefault();
  });

  $(document).on("click", ".vote-option-post", function (e) {
    var $t = $(this);
    $t.attr("data-loading", true);
    var data = {
      poll_id: $t.data("poll"),
      vote: $t.data("vote"),
      method: "poll_vote_ajax",
    };
    $t.ajax_req(
      function (r) {
        if (r.login_modal == true) {
          $("#signinModal").modal("show");
        } else if (r.success == true) {
          $t.addClass("selected-opt-vote");
          $.each(r.votes, function (opt, det) {
            var $elm = $("*[data-vote='" + opt + "']");
            $elm.addClass("voted-option");
            $elm.children(".poll-votes-count").text(det.votes);
            $elm.children(".vote-option-percent").css({
              width: det.percent + "%",
            });
          });
        } else {
          swal({
            text: r.msg,
            icon: "error",
            button: gbj.ok_text,
          });
        }
        $t.attr("data-loading", false);
      },
      null,
      data
    );
  });

  // post dropdown manager

  $('#single-post').on('shown.bs.dropdown', function () {
    let dropdownMenu = $(this).find('.dropdown-menu');
    dropdownMenu.find(".post-bk, .follow-post").unbind().on('click', function() {
      var $t = $(this);
      let method = $t.hasClass('follow-post') ? 'popover_post_follow_json' : 'popover_post_bk_json';
      $t.attr("data-loading", true);
      var data = {
        post_id: $t.data("post"),
        method: method,
      };
      $t.ajax_req(
        function (r) {
          if (r.login_modal == true) {
            $("#signinModal").modal("show");
          } else if (r.success == false) {
            swal({
              text: 'حصل خطأ',
              icon: "error",
              button: gbj.ok_text,
            });
          }
          $t.attr("data-loading", false);
        },
        null,
        data
      );
    });
  });

  var comment_initial = "";
  var comment_id = 0;

  $(document).on("click", ".edit-comment", function (e) {
    emojiForm++;
    comment_id = $(this).data("id");
    var $comment_div = $(".comment-p-id-" + comment_id);
    comment_initial = $comment_div.html();
    var comment_text = $comment_div.find(".comment-text").text();
    var $comment_form = $("#comment-form").clone();
    $comment_form.addClass("w-100");
    $comment_form.find("#emojoComment1").attr("id", "emojoComment" + emojiForm);
    $comment_form.find(".emojionearea").remove();

    $comment_form.append(
      '<input type="hidden" name="comment_id" value="' + comment_id + '"/>'
    );
    $comment_form.find("textarea").val(comment_text);

    $comment_form
      .find(".comment-btns")
      .append(
        '<button class="btn btn-light ml-2 cancel-comment-edit">إلغاء</button>'
      );

    $(".comment-p-id-" + comment_id).html($comment_form);

    $("#emojoComment" + emojiForm).emojioneArea({
      filters: {
        recent: false, // disable recent
      },
      attributes: {
        dir: "rtl",
      },
    });
  });

  $(document).on("click", ".cancel-comment-edit", function (e) {
    $(".comment-p-id-" + comment_id).html(comment_initial);
    e.preventDefault();
  });

  $(document).on("click", ".cancel-reply-comment", function (e) {
    $(".reply-form").html("");
    e.preventDefault();
  });

  $(document).on("keyup", "#new_comment_edit", function () {
    if ($(this).val() != current_comment) {
      $(".submit-comment-edit").prop("disabled", false);
    } else {
      $(".submit-comment-edit").prop("disabled", true);
    }
  });

  $(".send-post-notice").click(function (e) {
    var $this = $(this);
    $this.attr({
      "data-loading": true,
      disabled: true,
    });
    $this.ajax_req(function (r) {
      if (r.success == true) {
        swal({
          text: gbj.success_request,
          icon: "success",
          button: gbj.ok_text,
        });
      } else {
        swal({
          text: gbj.faild_request,
          icon: "error",
          button: gbj.ok_text,
        });
      }
      $this.attr({
        "data-loading": false,
        disabled: false,
      });
    });
    e.preventDefault();
  });

  $(".search-ajax").on("click", function () {
    if ($(window).width() > 544) {
      $(this).css({
        width: "80%",
      });
      $(".search-form").addClass("in-search-form");
    }
  });

  $(".search-ajax").keyup(function () {
    var $t = $(this);
    $(".instant-search-box").fadeIn(500);
    $.get(
      gbj.siteurl + "/ajax/ajax-html.php",
      {
        request: "instant-search",
        s: $t.val(),
        taxonomy: $(".select-category-search").val(),
      },
      function (data) {
        $(".instant-results").html(data);
      }
    );
  });

  $(".select-category-search").on("change", function () {
    var $t = $(this);
    $.get(
      gbj.siteurl + "/ajax/ajax-html.php",
      {
        request: "instant-search",
        s: $(".search-ajax").val(),
        taxonomy: $t.val(),
      },
      function (data) {
        $(".instant-results").html(data);
      }
    );
  });

  $(document).on("click", ".delete-media-file", function (e) {
    var $this = $(this);
    var id = $(this).attr("data-id");
    var data = {
      method: "delete_file_ajax",
      id: id,
    };
    swal({
      title: gbj.confirm_delete_text,
      icon: "warning",
      buttons: [gbj.cancel_text, gbj.ok_text],
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) {
        $this.ajax_req(
          function (r) {
            if (r.success == true) {
              swal({
                text: gbj.delete_success,
                icon: "success",
                button: gbj.ok_text,
              });
              $(".library-media[data-id='" + id + "']")
                .parent("div")
                .remove();
              $(".uploader-image-preview").hide();
            } else {
              swal({
                text: r.msg,
                icon: "error",
                button: gbj.ok_text,
              });
            }
          },
          null,
          data
        );
      } else {
        //
      }
    });

    e.preventDefault();
  });

  $(".select-category-search").on("change", function (e) {
    $("#search_post_type").val($(this).val());
  });

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

  $(".signup-home").click(function () {
    var $t = $(this);
    if ($t.hasClass("login-modal")) {
      $("#signinModal").modal("show");
      return;
    }
  });

  $(document).on("click", ".modal-link-post", function (e) {
    var $t = $(this);
    var post_id = $t.data("id");
    $("#loadModal").modal("show");
    $.get(
      gbj.siteurl + "/ajax/ajax-html.php",
      {
        request: "quick-post-view",
        post_id: post_id,
      },
      function (data) {
        $("#loadModal").html(data);
      }
    );

    e.preventDefault();
  });

  $(".opentermsModal").on("click", function (e) {
    var $t = $(this);
    $("#loadModal").modal("show");
    $.get(
      gbj.siteurl + "/ajax/ajax-html.php",
      {
        request: "taxonomy-terms",
        term: $t.data("term"),
        taxonomy: $t.data("taxonomy"),
      },
      function (data) {
        $("#loadModal").html(data);
      }
    );
    e.preventDefault();
  });

  $(".dropdown-menu-privacy .dropdown-item").click(function (e) {
    $($(this).parent("div").data("select")).html($(this).html());
    $($(this).data("input")).val($(this).data("value"));
    e.stopPropagation();
  });

  $.each($(".dropdown-menu-privacy"), function (i, v) {
    if (typeof $(this).data("select") != "undefined") {
      var data_select = $(this).data("select");
      var selected_opt = $(this).children(".selected-opt");
      $(data_select).html(selected_opt.html());
    }
  });

  $(document).on(
    "mouseenter",
    ".reaction-user:not(.reactions-count)",
    function (e) {
      $(".block-react-eem").show();
      $(".block-react-eem").removeClass("fadeOutUp").addClass("fadeInUp");
    }
  );

  $(document).on("mouseleave", ".reaction-area", function (e) {
    $(".block-react-eem").removeClass("fadeInUp").addClass("fadeOutUp");
  });

  $(document).on("click", ".remove-comment-thumb", function (e) {
    let form = $(this).parents('form');
    form.find(`[name="comment_attachment"]`).val("");
    form.find(".comment-attachment-dsp")
      .css({
        "background-image": "url()",
      })
      .hide();
    e.preventDefault();
  });

  $(document).on("click", ".react", function (e) {
    var $t = $(this);
    var post_id = $(".reaction-user").data("post");
    var data = {
      method: "un_reaction",
      reaction: $t.data("reaction"),
      post_id: post_id,
    };
    var intial_val = $(".reaction-user").attr("data-reacted");
    $(".reaction-btn").attr("data-reacted", $t.data("reaction"));
    $t.ajax_req(
      function (r) {
        if (r.success == true) {
          $(".block-react-eem")
            .removeClass("bounceInUp")
            .addClass("bounceOutUp");
          $(".reaction-btn").addClass("bounce");
          setTimeout(function () {
            $(".reaction-btn").removeClass("bounce");
          }, 1000);
          return;
        }
        $(".reaction-user").attr("data-reacted", intial_val);
      },
      null,
      data
    );
  });

  $(".un-subscribe-taxonomy").on("click", function (e) {
    var $t = $(this);
    var taxonomy = $t.data("taxonomy");
    var initial_val = $t.html();
    var data = {
      taxonomy: taxonomy,
      method: "un_subscribe_taxonomy",
    };

    var icon = $t.children("i");
    if ($t.attr("data-request") == "subscribe" || icon.hasClass("fa-check")) {
      $t.html('<i class="fas fa-times mr-2"></i>' + gbj.unsubscribe);
      $t.attr("data-request", "unsubscribe");
    } else {
      $t.html('<i class="fas fa-check mr-2"></i>' + gbj.subscribe);
      $t.attr("data-request", "subscribe");
    }

    $t.ajax_req(
      function (r) {
        if (r.success == false) {
          $t.html(initial_html);
          $t.attr("data-request", $t.data("request"));
          swal({
            text: r.msg,
            icon: "error",
            button: gbj.ok_text,
          });
        }
      },
      null,
      data
    );
  });

  $(document).on("hidden.bs.modal", "#addpostModal", function () {
    $(".choose-post-in").show();
    $(".choose-post-type").hide();
    $(".radio-select-post-type .custom-control-input").prop("checked", false);
  });

  $(document).mouseup(function (e) {
    var search_explored = $(".in-search-form");
    // if the target of the click isn't the container nor a descendant of the container
    if (
      !search_explored.is(e.target) &&
      search_explored.has(e.target).length === 0
    ) {
      search_explored.removeClass("in-search-form");
      $(".instant-search-box").fadeOut(500);
    }
    e.stopPropagation();
  });

  $(document).on("click", "#select-all-checkbox", function () {
    if ($(this).is(":checked")) {
      $(".check-box-action").prop("checked", true);
    } else {
      $(".check-box-action").prop("checked", false);
    }
  });

  // $(document).on("click", ".rate-content", function (e) {
  //   var t = $(this);
  //   var rate_val = t.data("value");
  //   var post_id = t.data("post");
  //   var data = {
  //     rate_value: rate_val,
  //     post_id: post_id,
  //     method: "un_rate_ajax",
  //   };
  //   t.ajax_req(
  //     function (r) {
  //       if (r.login_modal == true) {
  //         $("#signinModal").modal("show");
  //         $(".rating input[type='radio']").prop("checked", false);
  //       } else if (r.success === true) {
  //         $(".rating-progress").collapse("show");
  //         var stars = r.stars;
  //         for (var i = 1; i <= Object.keys(stars).length; i++) {
  //           if (typeof stars["" + i + ""]["percent"] == "number") {
  //             $(".progress-rate-percent-" + i).css({
  //               width: "" + stars["" + i + ""]["percent"] + "%",
  //             });
  //           } else {
  //             $(".progress-rate-percent-" + i).css({
  //               width: "0%",
  //             });
  //           }
  //         }
  //       } else {
  //       }
  //     },
  //     null,
  //     data
  //   );
  // });

  $(document).on("click", ".rate-content", function (e) {
    var t = $(this);
    var rate_val = t.data("value");
    var post_id = t.data("post");
    var data = {
        rate_value: rate_val,
        post_id: post_id,
        method: "un_rate_ajax",
    };

    t.ajax_req(
        function (r) {
            if (r.login_modal === true) {
                $("#signinModal").modal("show");
                $(".rating input[type='radio']").prop("checked", false);
            } else if (r.success === true) {
                $(".rating-progress").collapse("show");

                // Extract stars data
                var stars = r.stars;

                // Iterate over the stars object and update UI
                Object.keys(stars).forEach(function (starValue) {
                    var starData = stars[starValue];
                    var percentage = starData.percent || 0; // Default to 0 if not a number
                    var count = starData.rates || 0; // Default to 0 if not provided
                    if (typeof stars["" + starValue + ""]["percent"] == "number") {
                      $(".progress-rate-percent-" + starValue).css({
                        width: "" + stars["" + starValue + ""]["percent"] + "%",
                      }).text(`${percentage}%`);
                    } else {
                      $(".progress-rate-percent-" + starValue).css({
                        width: "0%",
                      }).text(`${percentage}%`);
                    }

                    // Update progress bar
                    // $(".progress-bar[data-value='" + starValue + "']").css({
                    //     width: percentage + "%",
                    // }).attr("aria-valuenow", percentage).text(percentage + "%");

                    // Update vote count
                    $(".vote-count[data-value='" + starValue + "']").text(`(${count})`);
                });
                setTimeout(function() {
                  $(`#commentModal`).modal('show');
                }, 2001);
            } else {
                // Handle errors or other responses if needed
            }
        },
        null,
        data
    );
  });

  $(document).on("click", ".open-lang-settings", function (e) {
    $("#langSettingsModal").modal("show");
    e.preventDefault();
  });

  $(".btn-sign-green,.btn-contact").click(function (e) {
    var btn = $(this);
    btn.attr({
      disabled: true,
      "data-loading": true,
    });
    $(this).ajax_req(function (r) {
      if (r.success == false) {
        if (typeof r.inputs_errors == "object") {
          $(r.inputs_errors).each(function (i, v) {
            $(v.selector).addClass("is-invalid");
            $(v.selector + "_error_txt").html(v.error);
          });
        }
        btn.attr({
          disabled: false,
          "data-loading": false,
        });
      } else {
        if (r.msg) {
          swal({
            title: r.msg,
            icon: "success",
            button: gbj.ok_text,
          }).then((value) => {
            window.location.href = gbj.siteurl + "/index.php";
            document.getElementById("contact-form").reset();
          });
        } else {
          if (r.to_instruction) {
            window.location.href = gbj.siteurl + "/dashboard/instructions";
          } else {
            window.location.href = gbj.siteurl + "/index.php";
          }
        }
      }
    });
    e.preventDefault();
  });

  $(document).on("click", ".toggle-signin-modal", function (e) {
    $("#signinModal").modal("show");
    e.preventDefault();
  });

  $(document).on("click", ".save-lang-settings", function (e) {
    var t = $(this);
    t.ajax_req(function (r) {
      if (r.success == true) {
        swal({
          title: r.msg,
          icon: "success",
          button: gbj.ok_text,
        }).then((value) => {
          location.reload();
        });
      } else {
        swal({
          text: r.msg,
          icon: "error",
        });
      }
    }, "#lang-form");
    e.preventDefault();
  });

  $(document).on("click", ".add-comment", function (e) {
    var t = $(this);
    t.ajax_req(function (r) {
      if (r.login_modal == true) {
        $("#signinModal").modal("show");
      } else if (r.success == true) {
        swal({
          text: r.msg,
          icon: "success",
          button: gbj.ok_text,
        });

        if(t.parents('form').is('#comment-form-2')) {
          $(`#commentModal`).modal('hide');
          let form2 = document.getElementById("comment-form-2");
          form2.reset();
        }

        let form = document.getElementById("comment-form");
        form.reset();
        let nodes = form.querySelector('.emojionearea-editor').childNodes;

        if(nodes.length > 0) {
          // nodes.remove();
          nodes.forEach((v) => {
            console.log(v);
            v.remove();
          });
        }

        if ($(".comment-p-id-" + r.comment_id).length !== 0) {
          $(".comment-p-id-" + r.comment_id).html(r.html);
        } else {
          if (r.comment_type == "reply" && r.comment_parent != 0) {
            $(".comment-p-id-" + r.comment_parent + " .users-replies").prepend(
              r.html
            );
            $(".reply-form").html("");
          } else {
            $(".get_comments > .form-row > .col-12").prepend(r.html);
          }
        }
      } else {
        swal({
          text: r.msg,
          icon: "error",
          button: gbj.ok_text,
        });
      }
    });
    e.preventDefault();
  });

  $(document).on("click", ".un_lock_post, .post-locked", function (e) {
    e.preventDefault();
    var t = $(this);
    var initial_class = t.attr("class");

    if (t.hasClass("post-locked")) {
      t.removeClass("post-locked");
    } else {
      t.addClass("post-locked");
    }

    var post_id = t.data("id");
    var data = {
      method: "un_lock_post_ajax",
      id: post_id,
    };
    t.ajax_req(
      function (r) {
        if (r.success == true) {
          if(t.is(`[data-action-change]`)) {
            t.attr('title', t.data('action-change')).find('span').text(t.data('action-change'));
          }
        } else {
          t.attr("class", initial_class);
          swal({
            text: r.msg,
            icon: "error",
            button: gbj.ok_text,
          });
        }
      },
      null,
      data
    );
  });

  $(document).on("click", ".un_trusted_post", function (e) {
    var t = $(this);
    var initial_class = t.attr("class");
    var tr_text = '';
    if (t.hasClass("post-trusted")) {
      t.removeClass("post-trusted");
      tr_text = t.data('untrusted');
    } else {
      t.addClass("post-trusted");
      tr_text = t.data('trusted');
    }

    var post_id = t.data("id");
    var data = {
      method: "merge_to_un_trusted",
      id: post_id,
    };
    t.ajax_req(
      function (r) {
        if (r.success == true) {
          t.attr('title', tr_text).find('span').text(tr_text);
        } else {
          t.attr("class", initial_class);
          swal({
            text: r.msg,
            icon: "error",
            button: gbj.ok_text,
          });
        }
      },
      null,
      data
    );
    e.preventDefault();
  });

  $(document).on("click", ".un_lock_comment", function (e) {
    var $t = $(this);
    var initial_class = $t.attr("class");
    if ($t.hasClass("post-locked")) {
      $t.removeClass("btn-warning post-locked").addClass("btn-success");
    } else {
      $t.removeClass("btn-success").addClass("btn-warning post-locked");
    }

    var comment_id = $t.data("id");
    var data = {
      method: "un_lock_comment_ajax",
      comment_id: comment_id,
    };
    $t.ajax_req(
      function (r) {
        if (r.success == true) {
        } else {
          $t.attr("class", initial_class);
          swal({
            text: r.msg,
            icon: "error",
            button: gbj.ok_text,
          });
        }
      },
      null,
      data
    );
    e.preventDefault();
  });

  $(document).on("click", ".un_bookmark", function (e) {
    var $t = $(this);

    if ($t.hasClass("post-bookmared")) {
      $t.removeClass("post-bookmared");
    } else {
      $t.addClass("post-bookmared");
    }

    var post_id = $t.data("id");

    var data = {
      method: "un_bookmark",
      post_id: post_id,
    };
    $t.ajax_req(
      function (r) {
        if (r.success == true) {
          swal({
            text: r.msg,
            icon: "success",
            button: gbj.ok_text,
          });
        } else {
          swal({
            text: r.msg,
            icon: "error",
            button: gbj.ok_text,
          });
        }
      },
      null,
      data
    );
    e.preventDefault();
  });

  $(document).on("click", ".delete-post-btn", function (e) {
    e.preventDefault();
    var $t = $(this);
    swal({
      title: gbj.confirm_delete_text,
      icon: "warning",
      buttons: [gbj.cancel_text, gbj.ok_text],
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) {
        var post_id = $t.data("id");
        var data = {
          post_id: post_id,
          method: "delete_post",
        };
        $t.ajax_req(
          function (r) {
            if (r.success == true) {
              $($t.data("remove")).remove();
              swal({
                title: gbj.delete_success,
                icon: "success",
                button: gbj.ok_text,
              }).then((value) => {
                if ($t.data("redirect")) {
                  window.location.href = $t.data("redirect");
                } else {
                  location.reload();
                }
              });
            } else {
              $.each(r.errors, function (index, val) {
                swal({
                  text: val.error,
                  icon: "error",
                  button: gbj.ok_text,
                }).then((value) => {
                  location.reload();
                });
              });
            }
          },
          null,
          data
        );
      } else {
        //
      }
    });
  });

  $(document).on("click", ".delete-comment-btn", function (e) {
    var $t = $(this);

    swal({
      title: gbj.confirm_delete_text,
      icon: "warning",
      buttons: [gbj.cancel_text, gbj.ok_text],
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) {
        var comment_id = $t.data("id");
        var data = {
          comment_id: comment_id,
          method: "delete_comment",
        };
        $t.ajax_req(
          function (r) {
            if (r.success == true) {
              swal({
                text: gbj.delete_success,
                icon: "success",
                button: gbj.ok_text,
              });
              $($t.data("remove")).remove();
            }
          },
          null,
          data
        );
      } else {
        //
      }
    });
  });

  $(document).on("click", ".send-msg-btn", function (e) {
    var t = $(this);
    t.ajax_req(function (r) {
      if (r.success == false) {
        swal({
          text: r.msg,
          icon: "error",
          button: gbj.ok_text,
        });
      } else {
        swal({
          title: r.msg,
          icon: "success",
          button: gbj.ok_text,
        }).then((value) => {
          if (t.hasClass("send-form")) {
            location.reload();
          } else {
            $("#send-message-form")[0].reset();
            $("#send-messageModal").modal("hide");
          }
        });
      }
    });
    e.preventDefault();
  });

  $(document).on("click", ".send-message-modal", function (e) {
    var data_user = $(this).data("user");
    $.get(
      gbj.siteurl + "/ajax/ajax-html.php",
      {
        request: "send-message-modal",
        user_id: data_user,
      },
      function (data) {
        console.log(data);
        if (data == "login-modal") {
          $("#signinModal").modal("show");
        } else {
          $("body").append(data);
        }
      }
    );
    e.preventDefault();
  });

  $(document).on("click", ".follow-btn, .follow-link", function (e) {
    var t = $(this);
    // if(t.is('a')) {
    //   e.preventDefault();
    // }
    if (t.hasClass("not-followed")) {
      t.removeClass("not-followed");
    } else {
      t.addClass("not-followed");
    }
    t.ajax_req(
      function (r) {
        if (r.login_modal == true) {
          $("#signinModal").modal("show");
        }
      },
      null,
      {
        method: "un_follow",
        user_id: t.data("user"),
      }
    );
    e.preventDefault();
  });

  $(document).on("click", ".delete-btn", function (e) {
    var t = $(this);
    t.ajax_req(function (r) {}, null, {
      method: "post_ajax",
      request: "delete_post",
      id: t.data("id"),
    });
    e.preventDefault();
  });
  $(".delete-account-request,.re-send-delete-code").on("click", function (e) {
    var $t = $(this);
    var data = {
      method: "delete_account_request",
    };
    $t.ajax_req(
      function (r) {
        swal({
          title: r.msg,
          icon: "success",
          button: gbj.ok_text,
        }).then((value) => {
          location.reload();
        });
      },
      null,
      data
    );
    e.preventDefault();
  });
  /*
    $('body').bind('cut copy paste', function (e) {
        e.preventDefault();
    });
    */
  $(".delete-cv-info").click(function (e) {
    var meta_id = $(this).data("id");
    var data = {
      method: "remove_meta_user_ajax",
      meta_id: meta_id,
    };
    swal({
      title: gbj.confirm_delete_text,
      icon: "warning",
      buttons: [gbj.cancel_text, gbj.ok_text],
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) {
        $(this).ajax_req(
          function (r) {
            if (r.success == true) {
              swal({
                title: r.msg,
                icon: "success",
                button: gbj.ok_text,
              }).then((value) => {
                $(".tr-meta-id-" + meta_id).remove();
              });
            }
          },
          null,
          data
        );
      } else {
        //
      }
    });

    e.preventDefault();
  });

  $(".edit-cv-info").click(function (e) {
    var $t = $(this);
    var data_info = $t.data("info");
    var data_form = $t.data("form");
    var data_id = $t.data("id");
    var input_prefix = $t.data("input-prefix");
    var find_form = $(data_form).find(".cv_form");
    find_form.addClass("activeF");
    find_form.append(
      '<input type="hidden" id="meta_id" name="meta_id" value="' +
        data_id +
        '"/>'
    );
    $("#info-modal").modal("show");
    $.each(data_info, function (i, v) {
      $(input_prefix + i).val(v);
      if (i == "privacy") {
        $(input_prefix + "dropdown-menu-privacy-selected").html(
          $(".dropdown-item[data-value='" + v + "']").html()
        );
      }
    });
    $(data_form).show();
    e.preventDefault();
  });

  $(".save-cv").click(function (e) {
    var $t = $(this);
    $t.attr({
      disabled: true,
      "data-loading": true,
    });
    $t.ajax_req(function (r) {
      swal({
        title: r.msg,
        icon: "success",
        button: gbj.ok_text,
      }).then((value) => {
        location.reload();
      });
      $t.attr({
        disabled: false,
        "data-loading": false,
      });
    }, ".cv_form.activeF");
    e.preventDefault();
  });
  $(".update-personal-info").click(function (e) {
    var $t = $(this);
    $t.attr({
      disabled: true,
      "data-loading": true,
    });
    $t.ajax_req(function (r) {
      if (r.success == true) {
        swal({
          title: r.msg,
          icon: "success",
          button: gbj.ok_text,
        }).then((value) => {
          location.reload();
        });
      } else {
        swal({
          text: r.msg,
          icon: "error",
          button: gbj.ok_text,
        });
      }
      $t.attr({
        disabled: false,
        "data-loading": false,
      });
    });
    e.preventDefault();
  });

  var data_form;
  var modal_title;

  $(".add-new-profile-info").click(function (e) {
    data_form = $(this).data("form");
    modal_title = $(this).data("title");
    $(data_form).find(".cv_form").addClass("activeF");
    $(".modal-title").text(modal_title);
    $("#info-modal").modal("show");
    $(data_form).show();
    e.preventDefault();
  });

  $("#info-modal").on("hidden.bs.modal", function (ev) {
    $(data_form).hide();
    $(".cv_form").removeClass("activeF");
    $(".cv_form").trigger("reset");
    $("#meta_id").remove();
  });

  $(".save-info").click(function (e) {
    var btn = $(this);
    btn.attr({
      disabled: true,
      "data-loading": true,
    });
    $(this).ajax_req(function (r) {
      if (r.success == false) {
        if (typeof r.inputs_errors == "object") {
          $(r.inputs_errors).each(function (i, v) {
            $(v.selector).addClass("is-invalid");
            $(v.selector + "_error_txt").html(v.error);
          });
        }
        btn.attr({
          disabled: false,
          "data-loading": false,
        });
      } else {
        location.reload();
      }
    });
    e.preventDefault();
  });

  $(".select-per-page, .select-post-type").on("change", function () {
    $("#filter-form").submit();
  });
  $(".update-notif-settings").click(function (e) {
    $(this).ajax_req(function (r) {
      if (r.success == true) {
        swal({
          title: r.msg,
          icon: "success",
          button: gbj.ok_text,
        }).then((value) => {
          location.reload();
        });
      } else {
        swal({
          text: r.msg,
          icon: "error",
          button: gbj.ok_text,
        });
      }
    });
    e.preventDefault();
  });

  $(".delete-account").on("click", function (e) {
    var $t = $(this);
    swal({
      title: gbj.confirm_delete_text,
      icon: "warning",
      buttons: [gbj.cancel_text, gbj.ok_text],
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) {
        $t.ajax_req(function (r) {
          if (r.success == true) {
            location.reload();
          } else {
            $.each(r.inputs_errors, function (i, v) {
              $(v.selector).addClass("is-invalid");
              $(v.selector + "-feedback").text(v.error);
            });
          }
        });
      } else {
        //
      }
    });

    e.preventDefault();
  });
  $(".filter-comment-type").click(function (e) {
    $("#comment_type").val($(this).data("value"));
    $("#filter-form").submit();
    e.preventDefault();
  });

  $(".btn-user-picture").click(function (e) {
    $("#upload_user_picture").click();
    e.preventDefault();
  });

  $("#upload_user_picture").on("change", function (e) {
    $(".save-info").prop("disabled", true);
    var t = $(this);
    t.upload_input(
      function (r) {
        if (r.success == true) {
          $("#user_picture").val(r.file_id);
          $(".btn-user-picture img").attr("src", r.file_url);
          $(".progress").hide();
          $(".save-info").click();
        }
        $(".save-info").prop("disabled", false);
      },
      $(".progress"),
      "user_attachment"
    );
  });

  $(".save-info").click(function (e) {
    $(this).ajax_req(function (r) {
      if (r.success == true) {
        swal({
          title: r.msg,
          icon: "success",
          button: gbj.ok_text,
        }).then((value) => {
          location.reload();
        });
      } else {
        $.each(r.inputs_errors, function (key, val) {
          $(val.selector).addClass("is-invalid");
          $(val.selector + "_error_txt").html(val.error);
        });
      }
    });
    e.preventDefault();
  });
  $(".posts-analytics-select").on("change", function (e) {
    var $t = $(this);
    $(".statistics-by-posts > .row").html(gbj.loading);
    $.get(
      gbj.siteurl + "/ajax/ajax-html.php",
      {
        request: "dashboard-ajax",
        data: "statistics",
        section: "posts",
        sort_by: $t.val(),
      },
      function (data) {
        $(".statistics-by-posts > .row").html(data);
      }
    );
  });

  $(".countries-analytics-select").on("change", function (e) {
    var $t = $(this);
    $(".statistics-by-country tbody").html(gbj.loading);
    $.get(
      gbj.siteurl + "/ajax/ajax-html.php",
      {
        request: "dashboard-ajax",
        data: "statistics",
        section: "countries",
        duration: $t.val(),
      },
      function (data) {
        $(".statistics-by-country tbody").html(data);
      }
    );
  });

  $(".statistics-analytics-select").on("change", function (e) {
    var $t = $(this);
    var data = {
      method: "get_analytics_nums",
      duration: $t.val(),
    };

    $t.find('option[value="' + $t.val() + '"]').attr("selected", true);

    var initial_content = $t.html();

    $t.html('<option selected="" disabled="">' + gbj.loading + "</option>");

    $t.ajax_req(
      function (r) {
        $(".trusted-posts-views-num").text(r.trusted_posts_views);
        $(".untrusted-posts-views-num").text(r.untrusted_posts_views);
        $(".share-posts-num").text(r.posts_shares);
        $(".all-views-analytics-num").text(r.all_views);
        $t.html(initial_content);
      },
      null,
      data
    );
  });
  $(".taxonomies-explore .nav-link").on("mouseenter", function () {
    // hide all showed ul
    $(".top-categories").hide();
    var taxo_type = $(this).data("taxonomy");
    var sub_taxo_elm = $(".top-categories-taxonomy-" + taxo_type);
    sub_taxo_elm.show();
    if (sub_taxo_elm.children("ul").length === 0) {
      $.get(
        gbj.siteurl + "/ajax/ajax-html.php",
        {
          request: "categories",
          taxo_type: taxo_type,
        },
        function (data) {
          $(".top-categories-taxonomy-" + taxo_type).html(data);
        }
      );
    }

    sub_taxo_elm.show();
  });

  $(".collapse-name").on("show.bs.collapse", function () {
    $(this).parents(".card").find(".card-header").addClass("visible-name");
  });

  $(".collapse-name").on("hide.bs.collapse", function () {
    $(this).parents(".card").find(".card-header").removeClass("visible-name");
  });

  $(".letter-filter-btn").on("click", function (e) {
    var $t = $(this);
    $("#letter-filter").val($t.data("letter"));
    $("#filter-form").submit();
    e.preventDefault();
  });
  $(".gender-filter-btn").on("click", function (e) {
    var $t = $(this);
    $("#gender-filter").val($t.data("gender"));
    $("#filter-form").submit();
    e.preventDefault();
  });

  $(document).on("click", ".open-all-tabs.open-names", function () {
    var $t = $(this);
    if ($t.hasClass("opened")) {
      $(".collapse-name").addClass("show");
      $t.toggleClass("opened closed");
      $t.text(gbj.close_all_text);
    } else {
      $(".collapse-name").removeClass("show");
      $t.toggleClass("closed opened");
      $t.text(gbj.open_all_text);
    }
  });

  $(".link-explore-taxos").click(function (e) {
    //e.preventDefault();
  });

  $(".upload-user-identify").click(function (e) {
    $("#upload_user_identify").click();
    e.preventDefault();
  });

  $(".upload-book").click(function (e) {
    $("#upload_book").click();
    e.preventDefault();
  });

  $("#upload_book").on("change", function (e) {
    var $t = $(this);
    
    $t.upload_input(
      function (r) {
        if (r.success == true) {
          $(".progress").hide();
          $(".media-file-show").append(
            '<li class="file-area list-group-item d-flex align-items-center">' +
              '<i class="fas fa-file mr-2"></i>' +
              "<span>" +
              r.file_original_name +
              "</span>" +
              '<span class="ml-auto remove-file" data-toggle="tooltip"><i class="fas fa-times"></i></span>' +
              '<input name="post_meta[books_ids][]" value="' +
              r.file_id +
              '" type="hidden"></li>'
          );

          const url = `${gbj.siteurl}/uploads/${r.file_dir}/${r.file_name}`; // Replace with your PDF file path

          const loadingTask = pdfjsLib.getDocument(url);

          loadingTask.promise
          .then(function (pdf) {
            // Get the first page of the PDF
            pdf.getPage(1).then(function (page) {

              let totalPages = pdf.numPages;
              let oldValue = parseInt($(`#book_pages`).val());

              $(`#book_pages`).val(oldValue + totalPages);

              const scale = 1.5; // Scale factor for image size
              const viewport = page.getViewport({ scale: scale });

              // Create a canvas to render the page
              const canvas = document.createElement("canvas");
              const context = canvas.getContext("2d");

              canvas.width = viewport.width;
              canvas.height = viewport.height;

              // Render the PDF page into the canvas
              const renderContext = {
                canvasContext: context,
                viewport: viewport,
              };
              page.render(renderContext).promise.then(function () {
                // Convert canvas to Blob (JPEG image)
                canvas.toBlob(function (blob) {
                  const file = new File([blob], "cover.jpg", { type: "image/jpeg" });
                
                  var formdata = new FormData();
                  formdata.append("file", file); // Make sure 'file' is the correct field name
                  formdata.append("method", "upload_ajax");
                  formdata.append("type", "user_attachment"); // Set type to image
                  formdata.append("file_category", 1); // Or the dynamic category if needed
                  console.log(formdata.get('file'));
                  $.ajax({
                    url: gbj.siteurl + "/ajax/ajax_service.php",
                    type: "POST",
                    data: formdata,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                      response = JSON.parse(response);
                      if (response.success) {
                        if($(`#upload_thumbnail_from_book`).length > 0) {
                          $(`#upload_thumbnail_from_book`).val(response.file_id);
                        }
                        // Handle the success response here
                        console.log("Image uploaded successfully:", response);
                      }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                      console.error("Error during the request:", textStatus, errorThrown);
                    }
                  });
                }, "image/jpeg");
              });
            });
          })
          .catch(function (error) {
            console.error("Error loading PDF: " + error);
          });

        }
      },
      $(".progress-book"),
      "book"
    );
  });

  $("#upload_user_identify").on("change", function (e) {
    $(".save-info").prop("disabled", true);
    var $t = $(this);
    $t.upload_input(
      function (r) {
        if (r.success == true) {
          $(".user-identify-field").html(
            '<a href="' + r.file_url + '">' + gbj.file + "</a>"
          );
          $("#user_identify").val(r.file_id);
          $(".progress-user-identify").hide();
        }
        $(".save-info").prop("disabled", false);
      },
      $(".progress-user-identify"),
      "user_attachment"
    );
  });

  $(".js-btn-send-cv-badge-order").click(function (e) {
    $(this).ajax_req(function (r) {
      if (r.success == true) {
        swal({
          title: r.msg,
          icon: "success",
          button: gbj.ok_text,
        }).then((value) => {
          location.reload();
        });
      } else {
        swal({
          text: r.msg,
          icon: "error",
          button: gbj.ok_text,
        });
      }
    });
    e.preventDefault();
  });

  $(document).on("click", ".remove-file", function (e) {
    var $t = $(this);
    $t.parent(".file-area").remove();
  });


  $(document).on("click", ".remove-audio-file", function (e) {
    var $t = $(this);
    if($t.siblings(`input[type="hidden"]`)) {
      $json = JSON.parse($t.siblings(`input[type="hidden"]`).val());
      if($json) {
        let id = $json.file_id;
        $.ajax({
          url: 'user-ajax.php',
          type: 'POST',
          data: {'id': id, 'action': 'deleteaudio'},
          success: function(response) {
            $resultJson = JSON.parse(response);
            if($resultJson.success == true) {
              $t.parents(".file-area").remove();
            } else {
              console.log("not deleted");
            }
          }
        });
      }
    }
  });

  $(document).on("click", ".open-media-library", function (e) {
    var addMediaTo = $(this).data("media");
    var addValTo = $(this).data("value");
    openMediaLibrary({
      media_dom: addMediaTo,
      value_dom: addValTo,
    });
    e.preventDefault();
  });

  $(document).on("click", ".open-custom-book", function (e) {
    openMediaLibrary({
      media_dom: addMediaTo,
      value_dom: addValTo,
    });
    e.preventDefault();
  });

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
});
