$(document).ready(function () {
  if (!window.location.href.match(/contents/)) {
    // select book part or article
    $(`#save_data_form [name="type"]`)
      .unbind()
      .on("change", function () {
        let form = $(this).parents("form");
        let value = $(this).val();
        if (value != 0) {
          form.find(`[data-action="toggle_label"]`).removeClass("d-none");
          if (value == "book") {
            form.find(`[data-action="toggle_show_book"]`).removeClass("d-none");
            form.find(`[data-action="toggle_show_article"]`).addClass("d-none");
            form
              .find(`[data-action="toggle_label"]`)
              .text("كتاب للمراجعة فقط بدون pdf");
          } else {
            form.find(`[data-action="toggle_label"]`).text("بدون الصورة");
            form
              .find(`[data-action="toggle_show_article"]`)
              .removeClass("d-none");
            form.find(`[data-action="toggle_show_book"]`).addClass("d-none");
          }
        } else {
          form.find(`[data-action]`).addClass("d-none");
        }
      });
  }

  $(`#save_data_form`)
    .unbind()
    .on("submit", function (e) {
      e.preventDefault();
      $(`.cover`)
        .removeClass("d-none")
        .find(".loader-nas")
        .addClass("start-loder");
      let form = $(this);
      let formData = new FormData(form.get(0));
      formData.append("action", `${form.attr("action")}`);
      $.ajax({
        url: "admin-ajax.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          $r = JSON.parse(response);
          $(`.cover`)
            .addClass("d-none")
            .find(".loader-nas")
            .removeClass("start-loder");
          swal({
            text: $r.msg,
            icon: $r.status,
            buttons: {
              حسنا: true,
            },
          }).then((clicked) => {
            if (clicked) {
              if ($r.redirect) {
                window.location = $r.redirect;
              } else {
                window.location.reload();
              }
            }
          });
        },
      });
    });

  // Scraping status tracking
  let scrapingInProgress = {};

  $(`[data-method="startScraping"]`)
    .unbind()
    .on("click", function (e) {
      e.preventDefault();
      const btn = $(this);
      const infoId = btn.data("id");
      const stopBtn = $(`[data-method="stopScraping"][data-id="${infoId}"]`);
      // Prevent multiple scraping requests for same ID
      if (scrapingInProgress[infoId]) {
        swal({
          text: "جاري جلب البيانات بالفعل لهذا العنصر",
          icon: "warning",
        });
        return;
      }

      stopBtn.parent("td").removeClass("d-none");

      // Start loading indicator
      btn
        .attr("disabled", "disabled")
        .html('<i class="fas fa-spinner fa-spin btn-spinner"></i>');

      // Mark scraping as in progress
      scrapingInProgress[infoId] = true;

      // Initialize scraping
      $.ajax({
        url: "scrap-ajax.php",
        type: "POST",
        data: {
          action: "initScraping",
          id: infoId,
        },
        success: function (response) {
          try {
            console.log("Init response:", response);
            const result = JSON.parse(response);
            if (result.status === "success") {
              // Start polling for progress
              setTimeout(() => pollScrapingProgress(infoId), 2000); // Give process time to start
            } else {
              handleScrapingError(infoId, result.msg);
            }
          } catch (e) {
            console.error("Parse error:", e);
            handleScrapingError(infoId, "خطأ في معالجة الاستجابة");
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX error:", status, error);
          handleScrapingError(infoId, "فشل في بدء عملية الجلب");
        },
      });
    });

  $(`[data-method="stopScraping"]`)
    .unbind()
    .on("click", function (e) {
      e.preventDefault();
      const btn = $(this);
      const infoId = btn.data("id");

      // Prevent multiple scraping requests for same ID
      if (!scrapingInProgress[infoId]) {
        swal({
          text: "لا يوجد عملية جلب بيانات لهذا الرابط",
          icon: "warning",
        });
        return;
      }

      // Start loading indicator
      btn.parent("td").addClass("d-none");

      // Initialize scraping
      $.ajax({
        url: "scrap-ajax.php",
        type: "POST",
        data: {
          action: "stopScrapingProgress",
          id: infoId,
        },
        success: function (response) {
          try {
            const result = JSON.parse(response);
            finishScraping(infoId, result);
          } catch (e) {
            console.error("Parse error:", e);
            handleScrapingError(infoId, "خطأ في معالجة الاستجابة");
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX error:", status, error);
          handleScrapingError(infoId, "فشل في بدء عملية الجلب");
        },
      });
    });

  function pollScrapingProgress(infoId) {
    $.ajax({
      url: "scrap-ajax.php",
      type: "POST",
      data: {
        action: "checkScrapingProgress",
        id: infoId,
        _: new Date().getTime(), // Prevent caching
      },
      success: function (response) {
        try {
          const result = JSON.parse(response);

          if (result.status === "completed") {
            finishScraping(infoId, result);
          } else if (result.status === "error") {
            handleScrapingError(infoId, result.msg);
          } else {
            // Update progress if available
            if (typeof result.progress !== "undefined") {
              updateProgress(infoId, result.progress);
            }
            // Continue polling with shorter interval for smoother updates
            setTimeout(() => pollScrapingProgress(infoId), 1000);
          }
        } catch (e) {
          console.error("Parse error:", e);
          handleScrapingError(infoId, "خطأ في معالجة الاستجابة");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", status, error);
        handleScrapingError(infoId, "فشل في التحقق من حالة الجلب");
      },
    });
  }

  function updateProgress(infoId, progress) {
    const btn = $(`[data-method="startScraping"][data-id="${infoId}"]`);
    // Add a progress bar class for visual feedback
    if (!btn.find(".progress-bar").length) {
      btn.addClass("relative overflow-hidden");
      btn.append(
        '<div class="progress-bar absolute left-0 top-0 h-full bg-green-500 opacity-20"></div>'
      );
    }
    // Update progress bar width
    btn.find(".progress-bar").css("width", `${progress}%`);
    // Update text
    btn.html(
      `<i class="fas fa-spinner fa-spin btn-spinner"></i><span class="btn-pourcent">${progress}%</span>`
    );
  }

  function finishScraping(infoId, result) {
    // Reset button and status
    const btn = $(`[data-method="startScraping"][data-id="${infoId}"]`);
    const stopBtn = $(`[data-method="stopScraping"][data-id="${infoId}"]`);
    btn.removeAttr("disabled").html('<i class="fas fa-magnet"></i>');
    delete scrapingInProgress[infoId];
    stopBtn.parent("td").addClass("d-none");
    // Show completion message
    swal({
      text: result.msg,
      icon: "success", //result.status,
      buttons: {
        حسنا: true,
      },
    });
    // .then((clicked) => {
    //   if (clicked) {
    //     if(result.redirect) {
    //       window.location = result.redirect;
    //     } else {
    //       window.location.reload();
    //     }
    //   }
    // });
    checkAndExecuteProgram();
  }

  function handleScrapingError(infoId, message) {
    console.error("Scraping error for ID " + infoId + ":", message);

    // Reset button and status
    const btn = $(`[data-method="startScraping"][data-id="${infoId}"]`);
    const stopBtn = $(`[data-method="stopScraping"][data-id="${infoId}"]`);
    btn.removeAttr("disabled").html('<i class="fas fa-magnet"></i>');
    delete scrapingInProgress[infoId];
    stopBtn.parent("td").addClass("d-none");

    // Show error message
    swal({
      text: message,
      icon: "error",
      buttons: {
        حسنا: true,
      },
    });
  }

  // end of scraping

  $("#addProgramFetchModal").on("shown.bs.modal", function (event) {
    let btn = event.relatedTarget;

    $(`#program-info-id`).val($(btn).data("infoid"));
    $("#program_date_input").multiDatesPicker({
      dateFormat: "yy-mm-dd",
    });
    // Toggle form fields based on schedule type selection
    $('input[name="schedule_type"]').change(function () {
      if ($("#every_day").is(":checked")) {
        $("#program_date_section")
          .addClass("d-none")
          .find("input")
          .removeAttr("name");
      } else if ($("#program_date").is(":checked")) {
        $("#program_date_section")
          .removeClass("d-none")
          .find("input")
          .attr("name", "days");
        $("#every_day_time").addClass("d-none");
      }
    });
  });

  $(`#save-program`)
    .unbind()
    .on("submit", function (e) {
      e.preventDefault();
      $(`.cover`)
        .removeClass("d-none")
        .find(".loader-nas")
        .addClass("start-loder");

      const userTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'user_timezone';
      input.value = userTimeZone;
      this.appendChild(input);


      let form = $(this);
      let formData = new FormData(form.get(0));
      formData.append("action", `${form.attr("action")}`);
      $.ajax({
        url: "admin-ajax.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          $r = JSON.parse(response);
          $(`.cover`)
            .addClass("d-none")
            .find(".loader-nas")
            .removeClass("start-loder");
          swal({
            text: $r.msg,
            icon: $r.status,
            buttons: {
              حسنا: true,
            },
          }).then((clicked) => {
            if (clicked) {
              if ($r.redirect) {
                window.location = $r.redirect;
              } else {
                window.location.reload();
              }
            }
          });
        },
      });
    });

  // let getCloserFormData = new FormData();
  // getCloserFormData.append('action', 'getCloserProgram');

  function checkAndExecuteProgram() {
    $.ajax({
      url: "admin-ajax.php",
      method: "POST",
      data: { action: "getCloserProgram" },
      dataType: "json",
      success: function (data) {
        console.log(data);
        if (data.status === "success") {
          // Schedule next program execution
          setTimeout(function () {
            executeProgram(data.program);
          }, data.delay_in_ms);
        } else {
          // If no programs found, check again in 5 minutes
          setTimeout(checkAndExecuteProgram, 5 * 60 * 1000);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error checking program:", error);
        // On error, retry after 1 minute
        setTimeout(checkAndExecuteProgram, 60 * 1000);
      },
    });
  }

  function executeProgram(program) {
    $(
      `button[data-method="startScraping"][data-id="${program.post_info_id}"]`
    ).trigger("click");
  }

  checkAndExecuteProgram();

  $("#account").select2({
    tags: true,
    dir: "rtl",
    width: "100%",
    language: "ar",
    dropdownAutoWidth: true,
    dropdownParent: $("#post-info-author-select2"),
  });

  $("#users_family").select2({
    // tags: true,
    dir: "rtl",
    width: "100%",
    language: "ar",
    // allowClear: true,
    dropdownAutoWidth: true,
    dropdownParent: $("#boot-account-select2"),
  });

  $("#users").select2({
    // tags: true,
    dir: "rtl",
    width: "100%",
    language: "ar",
    // allowClear: true,
    dropdownAutoWidth: true,
    dropdownParent: $("#boot-account-user-select2"),
  });

  $("#comments").select2({
    // tags: true,
    dir: "rtl",
    width: "100%",
    language: "ar",
    // allowClear: true,
    dropdownAutoWidth: true,
    dropdownParent: $("#boot-comments-select2"),
  });

  // fetch details

  $("#fetchDetailsModal").on("shown.bs.modal", function (event) {
    let btn = event.relatedTarget;
    let info_id = $(btn).data("infoid");
    $.ajax({
      url: "admin-ajax.php",
      type: "POST",
      data: { action: "getfetchdetails", info_id: info_id },
      success: function (response) {
        $(`#fetchDetailsModal .modal-body .table tbody`).html(``);
        let datas = response ? JSON.parse(response) : [];
        if (datas) {
          $(`#fetchDetailsModal .modal-body .table tbody`).append(`
            <tr>
              <td>${datas.date}</td>
              <td>${datas.success}</td>
              <td>${datas.fails}</td>
              <td>${datas.exists}</td>
            </tr>
          `);
        } else {
          $(`#fetchDetailsModal .modal-body .table tbody`).append(`
            <tr>
              <td colspan="4" class="text-center">لا توجد بيانات</td>
            </tr>
          `);
        }
      },
    });
  });

  // get boot family
  $("#bootFamilyModal").on("shown.bs.modal", function (event) {
    let btn = event.relatedTarget;
    let boot_id = $(btn).data("id");
    $.ajax({
      url: "admin-ajax.php",
      type: "POST",
      data: { action: "getbootfamily", boot_id: boot_id },
      success: function (response) {
        $(`#bootFamilyModal .modal-body`).html(response);
      },
    });
  });

  if ($("#emojoComment1").length > 0) {
    var emojioneArea = $("#emojoComment1").emojioneArea({
      pickerPosition: "bottom", // Position du sélecteur d'emojis
      tonesStyle: "bullet", // Style pour les variations de tons de peau
      placeholder: "اضف تعليق ...",
      events: {
        // Gérer l'événement 'keypress' pour capturer la touche Entrée
        keyup: function (editor, event) {
          if (event.key === "Enter" && !event.shiftKey) {
            // Si l'utilisateur appuie sur "Entrée" sans majuscule
            event.preventDefault(); // Empêche l'ajout d'un saut de ligne
            sendComment(); // Appelle la fonction pour envoyer le commentaire
          }
        },
      },
    });
    // Fonction d'envoi du commentaire
    function sendComment() {
      var comment = emojioneArea[0].emojioneArea.getText(); // Récupère le texte sans HTML
      if (comment.trim() !== "") {
        // Vérifie que le commentaire n'est pas vide
        console.log("Commentaire envoyé :", comment); // Affiche le commentaire dans la console
        $("#emojoComment1").parents("form").find(".add-comment").click();
      }
    }
  }

  if ($("#author-account").length > 0) {
    $("#author-account").select2({
      tags: false,
      dir: "rtl",
      width: "100%",
      language: "ar",
      dropdownAutoWidth: true,
      dropdownParent: $("#author-account-select2"),
    });
  }

  if ($(`.author-change-stat`).length > 0) {
    // change stat
    $(`.author-change-stat`)
      .unbind()
      .on("click", function (e) {
        e.preventDefault();
        let btn = $(this);
        if (confirm("هل انت متأكد")) {
          $.ajax({
            url: `/user-ajax.php`,
            type: "POST",
            data: {
              action: "authorchangestat",
              stat: btn.data("stat"),
              id: btn.data("id"),
            },
            success: function (response) {
              if (response.match(/ok/)) {
                swal({
                  text: "العملية تمت بنجاج",
                  icon: "success",
                  buttons: {
                    حسنا: true,
                  },
                }).then((clicked) => {
                  if (clicked) {
                    window.location.reload();
                  }
                });
              } else {
                swal({
                  text: "حدث خطأ",
                  icon: "error",
                  buttons: {
                    حسنا: true,
                  },
                });
              }
            },
          });
        }
      });
  }

  // boot work

  class BootAutomation {
    constructor() {
      this.activeProcesses = new Map();
      this.initializeEventListeners();
    }

    initializeEventListeners() {
      $(document).on("click", ".boot-change-stat", (e) => {
        e.preventDefault();
        const btn = $(e.currentTarget);
        const bootId = btn.data("id");
        const currentStat = parseInt(btn.attr("data-stat"));

        if (currentStat === 0) {
          this.startBoot(btn);
        } else {
          this.stopBoot(btn);
        }
      });
    }

    async startBoot(btn) {
      const bootId = btn.data("id");

      try {
        // Start the boot process
        const response = await $.ajax({
          url: "boot-worker.php",
          type: "POST",
          data: {
            action: "start",
            id: bootId,
          },
        });

        if (response.success) {
          // Update button appearance
          this.updateButtonState(btn, true);

          // Start progress monitoring
          this.monitorProgress(bootId, btn);
        } else {
          swal({
            text: response.message || "Failed to start boot process",
            icon: "error",
            buttons: { حسنا: true },
          });
        }
      } catch (error) {
        console.error("Error starting boot:", error);
        swal({
          text: "Error starting boot process",
          icon: "error",
          buttons: { حسنا: true },
        });
      }
    }

    async stopBoot(btn) {
      const bootId = btn.data("id");

      try {
        const response = await $.ajax({
          url: "boot-worker.php",
          type: "POST",
          data: {
            action: "stop",
            id: bootId,
          },
        });

        if (response.success) {
          // Clear monitoring interval
          if (this.activeProcesses.has(bootId)) {
            clearInterval(this.activeProcesses.get(bootId));
            this.activeProcesses.delete(bootId);
          }

          // Update button appearance
          this.updateButtonState(btn, false);
        }
      } catch (error) {
        console.error("Error stopping boot:", error);
        swal({
          text: "Error stopping boot process",
          icon: "error",
          buttons: { حسنا: true },
        });
      }
    }

    monitorProgress(bootId, btn) {
      // Create progress monitoring interval
      const intervalId = setInterval(async () => {
        try {
          const status = await $.ajax({
            url: "boot-worker.php",
            type: "POST",
            data: {
              action: "status",
              id: bootId,
            },
          });

          // Update progress UI
          this.updateProgressUI(status, btn);

          // Check if process is complete
          if (["completed", "failed", "stopped"].includes(status.status)) {
            clearInterval(intervalId);
            this.activeProcesses.delete(bootId);

            if (status.status === "completed") {
              this.updateAnalytics(status.analytics);
            }
          }
        } catch (error) {
          console.error("Error monitoring progress:", error);
          clearInterval(intervalId);
          this.activeProcesses.delete(bootId);
        }
      }, 5000); // Check every 5 seconds

      this.activeProcesses.set(bootId, intervalId);
    }

    updateButtonState(btn, isActive) {
      btn
        .attr("data-stat", isActive ? 1 : 0)
        .data("stat", isActive ? 1 : 0)
        .removeClass("btn-danger btn-success")
        .addClass(isActive ? "btn-danger" : "btn-success")
        .attr("title", isActive ? "موقوف عن العمل" : "تشغيل البوت")
        .find("i")
        .removeClass("fa-stop-circle fa-play-circle")
        .addClass(isActive ? "fa-stop-circle" : "fa-play-circle");
    }

    updateProgressUI(status, btn) {
      // Add progress indicator near the button
      let progressElement = btn.siblings(".boot-progress");
      if (!progressElement.length) {
        progressElement = $('<div class="boot-progress"></div>');
        btn.after(progressElement);
      }

      progressElement.html(`Progress: ${status.progress}%`);
    }

    updateAnalytics(analytics) {
      if (!analytics) return;

      const modal = $("#bootAnalyticModal");
      modal.find(".modal-body").empty();

      // Update analytics UI (reuse existing analytics update code)
      // ... (Keep your existing analytics update code here)
    }
  }

  // Initialize boot automation
  $(document).ready(() => {
    window.bootAutomation = new BootAutomation();
  });

  // $(document).on('click', '.boot-change-stat', function(e) {
  //   e.preventDefault();
  //   let btn = $(this);

  //   // Get the stat value directly from the attribute instead of .data()
  //   const currentStat = btn.attr('data-stat');

  //   $.ajax({
  //     url: 'admin-ajax.php',
  //     type: "POST",
  //     data: {
  //       action: 'bootstartwork',
  //       stat: currentStat,
  //       id: btn.attr('data-id')
  //     },
  //     success: function(response) {
  //       try {
  //         const data = typeof response === 'string' ? JSON.parse(response) : response;

  //         if (data.success) {
  //           if (typeof data.stat === "number") {
  //             const isActive = data.stat > 0;
  //             btn
  //               // Update both the attribute AND the data cache
  //               .attr('data-stat', data.stat)
  //               .data('stat', data.stat) // Update jQuery's data cache
  //               .removeClass('btn-danger btn-success')
  //               .addClass(isActive ? 'btn-danger' : 'btn-success')
  //               .attr('title', isActive ? 'موقوف عن العمل' : 'تشغيل البوت')
  //               .find('i')
  //               .removeClass('fa-stop-circle fa-play-circle')
  //               .addClass(isActive ? 'fa-stop-circle' : 'fa-play-circle');
  //           }

  //           if (data.analytics) {
  //             const modal = $('#bootAnalyticModal');

  //             modal.find('.modal-body').html(``);

  //             if (data.analytics.follow_accounts) {
  //               modal.find('.modal-body').append(`
  //                 <table class="table table-bordered follow_accounts">
  //                   <thead></thead>
  //                   <tbody></tbody>
  //                 </table>
  //               `);
  //               const analytics = data.analytics.follow_accounts;
  //               const familiesSuccess = JSON.parse(analytics.families_success);
  //               const usersSuccess = JSON.parse(analytics.users_success);

  //               modal.find('.modal-body .follow_accounts thead').html(`
  //                 <tr>
  //                   <th>عدد الحسابات المتفاعلة</th>
  //                   <th>عدد المتابعات</th>
  //                   <th>نسبة اتمام العملية</th>
  //                   <th>اخر تاريخ للتفاعل</th>
  //                 </tr>
  //               `);

  //               modal.find('.modal-body .follow_accounts tbody').html(`
  //                 <tr>
  //                   <td>${familiesSuccess.length}</td>
  //                   <td>${Object.keys(usersSuccess).length}</td>
  //                   <td>${analytics.progress}</td>
  //                   <td>${analytics.updated_at}</td>
  //                 </tr>
  //               `);
  //             }

  //             if (data.analytics.add_comments) {
  //               modal.find('.modal-body').append(`
  //                 <table class="table table-bordered add_comments">
  //                   <thead></thead>
  //                   <tbody></tbody>
  //                 </table>
  //               `);
  //               const analytics_comments = data.analytics.add_comments;
  //               const terminated_posts = JSON.parse(analytics_comments.terminated_posts);
  //               const terminated_commentators = JSON.parse(analytics_comments.terminated_commentators);

  //               modal.find('.modal-body .add_comments thead').html(`
  //                 <tr>
  //                   <th>عدد الحسابات المتفاعلة</th>
  //                   <th>عدد المواضيع</th>
  //                   <th>نسبة اتمام العملية</th>
  //                   <th>عدد التعليقات</th>
  //                 </tr>
  //               `);
  //               modal.find('.modal-body .add_comments tbody').html(`
  //                 <tr>
  //                   <td>${Math.round(analytics_comments.commentProgressNumber / (terminated_posts.length * 2))}</td>
  //                   <td>${terminated_posts.length * 2}</td>
  //                   <td>${analytics_comments.commentProgress}</td>
  //                   <td>${analytics_comments.commentProgressNumber}</td>
  //                 </tr>
  //               `);
  //             }

  //             if (data.analytics.add_reviews) {
  //               modal.find('.modal-body').append(`
  //                 <table class="table table-bordered add_reviews">
  //                   <thead></thead>
  //                   <tbody></tbody>
  //                 </table>
  //               `);
  //               const analytics_reviews = data.analytics.add_reviews;
  //               const terminated_posts = JSON.parse(analytics_reviews.terminated_posts);
  //               const terminated_reviewers = JSON.parse(analytics_reviews.terminated_reviewers);

  //               modal.find('.modal-body .add_reviews thead').html(`
  //                 <tr>
  //                   <th>عدد الحسابات المتفاعلة</th>
  //                   <th>عدد المواضيع</th>
  //                   <th>نسبة اتمام العملية</th>
  //                   <th>عدد التقييمات</th>
  //                 </tr>
  //               `);
  //               modal.find('.modal-body .add_reviews tbody').html(`
  //                 <tr>
  //                   <td>${Math.round(analytics_reviews.reviewProgressNumber / (terminated_posts.length))}</td>
  //                   <td>${terminated_posts.length * 2}</td>
  //                   <td>${analytics_reviews.reviewProgress}</td>
  //                   <td>${analytics_reviews.reviewProgressNumber}</td>
  //                 </tr>
  //               `);
  //             }
  //             //
  //             if (data.analytics.add_previews) {
  //               modal.find('.modal-body').append(`
  //                 <table class="table table-bordered add_previews">
  //                   <thead></thead>
  //                   <tbody></tbody>
  //                 </table>
  //               `);
  //               const analytics_previews = data.analytics.add_previews;
  //               const terminated_posts_previews = JSON.parse(analytics_previews.terminated_posts_previews);
  //               const terminated_previewers = JSON.parse(analytics_previews.terminated_previewers);

  //               modal.find('.modal-body .add_previews thead').html(`
  //                 <tr>
  //                   <th>عدد الحسابات المتفاعلة</th>
  //                   <th>عدد المواضيع</th>
  //                   <th>نسبة اتمام العملية</th>
  //                   <th>عدد المشاهدات</th>
  //                 </tr>
  //               `);
  //               modal.find('.modal-body .add_previews tbody').html(`
  //                 <tr>
  //                   <td>${Math.round(analytics_previews.previewProgressNumber / (terminated_posts_previews.length))}</td>
  //                   <td>${terminated_posts_previews.length * 2}</td>
  //                   <td>${analytics_previews.previewProgress}</td>
  //                   <td>${analytics_previews.previewProgressNumber}</td>
  //                 </tr>
  //               `);
  //             }
  //             //
  //             if (data.analytics.books_and_subject_tools) {
  //               modal.find('.modal-body').append(`
  //               <table class="table table-bordered books_and_subject_tools">
  //                   <thead></thead>
  //                   <tbody></tbody>
  //               </table>
  //               `);
  //               const analytics_books = data.analytics.books_and_subject_tools;
  //               const terminated_books_manager = JSON.parse(analytics_books.terminated_books_manager);
  //               const listen = JSON.parse(analytics_books.listen);
  //               const preview = JSON.parse(analytics_books.preview);
  //               const download = JSON.parse(analytics_books.download);

  //               modal.find('.modal-body .books_and_subject_tools thead').html(`
  //               <tr>
  //                   <th>عدد الحسابات المتفاعلة</th>
  //                   <th>عدد المواضيع</th>
  //                   <th>المسموعات</th>
  //                   <th>المعاينات</th>
  //                   <th>التحميلات</th>
  //                   <th>نسبة اتمام العملية</th>
  //                   <th>عدد العمليات</th>
  //               </tr>
  //               `);
  //               modal.find('.modal-body .books_and_subject_tools tbody').html(`
  //               <tr>
  //                   <td>${Math.round(analytics_books.bookProgressNumber / (terminated_books_manager.length))}</td>
  //                   <td>${terminated_books_manager.length * 2}</td>
  //                   <td>
  //                       <span class="d-block mb-2">
  //                           <i class="mr-2 fas fa-check-circle text-success"></i>${listen.success}
  //                       </span>
  //                       <span class="d-block mb-2">
  //                           <i class="mr-2 fas fa-times-circle text-danger"></i>${listen.fails}
  //                       </span>
  //                   </td>
  //                   <td>
  //                       <span class="d-block mb-2">
  //                           <i class="mr-2 fas fa-check-circle text-success"></i>${preview.success}
  //                       </span>
  //                       <span class="d-block mb-2">
  //                           <i class="mr-2 fas fa-times-circle text-danger"></i>${preview.fails}
  //                       </span>
  //                   </td>
  //                   <td>
  //                       <span class="d-block mb-2">
  //                           <i class="mr-2 fas fa-check-circle text-success"></i>${download.success}
  //                       </span>
  //                       <span class="d-block mb-2">
  //                           <i class="mr-2 fas fa-times-circle text-danger"></i>${download.fails}
  //                       </span>
  //                   </td>
  //                   <td>${analytics_books.bookProgress}</td>
  //                   <td>${analytics_books.bookProgressNumber}</td>
  //               </tr>
  //               `);
  //             }
  //             modal.modal('show');
  //           }
  //         } else {
  //           swal({
  //             text: data.msg,
  //             icon: "error",
  //             buttons: {
  //               حسنا: true,
  //             },
  //           });
  //         }
  //       } catch (error) {
  //         console.error('Error processing response:', error);
  //         swal({
  //           text: 'حدث خطأ في معالجة البيانات',
  //           icon: "error",
  //           buttons: {
  //             حسنا: true,
  //           },
  //         });
  //       }
  //     },
  //     error: function() {
  //       swal({
  //         text: 'حدث خطأ في الاتصال بالخادم',
  //         icon: "error",
  //         buttons: {
  //           حسنا: true,
  //         },
  //       });
  //     }
  //   });
  // });
});
