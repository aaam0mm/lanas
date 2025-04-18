$(function() {
  $(document).on("click", ".short-link", function (e) {
    var $t = $(this);
    var comment_id = $t.data("comment");
    var data = {
      request: "complain-form",
      post_id: $t.data("post"),
    };
    if (comment_id) {
      data.comment_id = comment_id;
    }
    $("#shortLink").modal("show");
    // $.get(gbj.siteurl + "/ajax/ajax-html.php", data, function (data) {
    //   $("#shortLink").html(data);
    // });
    e.preventDefault();
  });


  $('#shortLink').on('shown.bs.modal', function (event) {
    $(`#urlshortform`).unbind().on('submit', function(e) {
      e.preventDefault();
      let form = $(this);
      $.ajax({
        url: 'user-ajax.php',
        type: 'POST',
        data: {'originalUrl': window.location.pathname, 'action' : form.attr('action')},
        success: function(response) {
          $r = JSON.parse(response);
          console.log($r);
          form.find('input').val($r.short);
          $(`#copyShortLink`).attr('data-short', $r.short);
        }
      });
      
    });

    $(`#urlshortform [type="submit"]`).click();

    function enableCopying() {
      // Force enable text selection and copying
      document.body.onselectstart = null;
      document.body.onmousedown = null;
      document.body.oncontextmenu = null;

      // Optionally, apply it to specific elements
      $('#copyShortLink').off('mousedown selectstart contextmenu');
      $('#shortenedUrl').off('mousedown selectstart contextmenu');
  }

  function copyToClipboard() {
      enableCopying();  // Make sure copying is enabled first

      var textToCopy = $(`#copyShortLink`).data('short');
      
      if (navigator.clipboard) {
          navigator.clipboard.writeText(textToCopy).then(function() {
              alert("تم نسخ الرابط بنجاح!"); // Alert on successful copy
          }).catch(function(err) {
              alert("فشل النسخ: " + err); // Alert if there was an error
          });
      } else {
          // Fallback for older browsers using execCommand
          var tempInput = document.createElement("input");
          tempInput.style.position = "absolute";
          tempInput.style.left = "-9999px";
          tempInput.value = textToCopy;
          document.body.appendChild(tempInput);
          tempInput.select();
          tempInput.setSelectionRange(0, 99999);  // For mobile compatibility

          try {
              var successful = document.execCommand("copy");
              if (successful) {
                  alert("تم نسخ الرابط بنجاح!");
              } else {
                  alert("فشل النسخ");
              }
          } catch (err) {
              alert("فشل النسخ: " + err);
          }

          document.body.removeChild(tempInput);  // Clean up the temporary input
      }
    }
    $(`#copyShortLink`).on('click', function() {
      copyToClipboard();
    });

  });

  // book

  $('#book_author').on('input', function() {
    const searchTerm = $(this).val();
    $('#book_author').attr('data-value', searchTerm);
    $(`[name="post_meta[book_author]"]`).val(searchTerm);
    let authorList = $('#author-list');
    if(searchTerm == '') {
      authorList.addClass('d-none');
    } else {
      authorList.removeClass('d-none');
    }
    // AJAX request to the current file (book.php)
    $.ajax({
        url: 'user-ajax.php', // Current file
        type: 'POST',
        data: { action: 'authorsearchname', value: searchTerm },
        dataType: 'JSON',
        success: function(response) {
            // Update the author list
            authorList.empty(); // Clear current list
            
            if (response.length) {
                response.forEach(function(author) {
                    authorList.append(
                        `<button data-value="${author.id}" type="button" class="list-group-item list-group-item-action">
                            ${author.name}
                        </button>`
                    );
                });
            }

            $(`#author-list button`).unbind().on('click', function(e) {
              e.preventDefault();
              let btn = $(this);
              $('#book_author').val(btn.text().trim()).attr('data-value', btn.data('value'));
              $(`[name="post_meta[book_author]"]`).val(btn.data('value'));
              $(`#author-list`).addClass('d-none');
            });

        },
        // error: function() {
        // 		alert('An error occurred while fetching authors.');
        // }
    });
  });

  $('#is_book_author').on('change', function(e) {
    $('#book_author').val("")
    let value = $(this).val();
    if (value == 'no') {
        // Show the author dropdown and make it interactive
        $('#book-author-form-data').removeClass('d-none');
        // $('#author').removeAttr('disabled');
        
        // Reset any inline styles applied previously
        $('#book_author').css('pointer-events', '').css('background-color', '');
    } else if (value > 0) {
        // Show the author dropdown
        $('#book-author-form-data').removeClass('d-none');
        
        // Find the option marked with data-selected="true"
        let selectedOption = $('#book_author').find('button[data-selected="true"]');
        
        if (selectedOption.length > 0) {
            // Set the value using Select2 API
            $('#book_author').val(selectedOption.val()).trigger('change');
        } else {
          let unameOption = $(`[data-uname]`);
          let unameValue = unameOption.data('uname');

          if (unameValue) {
              // If data-uname is found, set it as the value
              $('#book_author').val(unameValue).trigger('change');
          } else {
              console.error("No value found for data-uname attribute.");
              // Optionally, handle the case when no value is found
          }
        }
        // Destroy Select2 to disable interaction and apply custom styles
        $('#book_author').css('pointer-events', 'none').css('background-color', '#e9ecef');
        
    } else {
        // Hide the author dropdown
        $('#book-author-form-data').addClass('d-none');

        $('#book_author').css('pointer-events', '').css('background-color', '');  // Reset styles
    }
  });

  // translators

  $('#book_translator').on('input', function() {
    const searchTerm = $(this).val();
    $('#book_translator').attr('data-value', searchTerm);
    $(`[name="post_meta[book_translator]"]`).val(searchTerm);
    let translatorList = $('#translator-list');
    if(searchTerm == '') {
      translatorList.addClass('d-none');
    } else {
      translatorList.removeClass('d-none');
    }
    // AJAX request to the current file (book.php)
    $.ajax({
        url: 'user-ajax.php', // Current file
        type: 'POST',
        data: { action: 'translatorsearchname', value: searchTerm },
        dataType: 'JSON',
        success: function(response) {
            // Update the translator list
            translatorList.empty(); // Clear current list
            
            if (response.length) {
                response.forEach(function(translator) {
                    translatorList.append(
                        `<button data-value="${translator.id}" type="button" class="list-group-item list-group-item-action">
                            ${translator.name}
                        </button>`
                    );
                });
            }

            $(`#translator-list button`).unbind().on('click', function(e) {
              e.preventDefault();
              let btn = $(this);
              $('#book_translator').val(btn.text().trim()).attr('data-value', btn.data('value'));
              $(`[name="post_meta[book_translator]"]`).val(btn.data('value'));
              $(`#translator-list`).addClass('d-none');
            });

        },
        // error: function() {
        // 		alert('An error occurred while fetching translators.');
        // }
    });
  });

  $('#is_book_translator').on('change', function(e) {
    $('#book_translator').val("")
    let value = $(this).val();
    if (value == 'new') {
        // Show the translator dropdown and make it interactive
        $('#book-translator-form-data').removeClass('d-none');
        // $('#translator').removeAttr('disabled');
        
        // Reset any inline styles applied previously
        $('#book_translator').css('pointer-events', '').css('background-color', '');
    } else if (value > 0) {
        // Show the translator dropdown
        $('#book-translator-form-data').removeClass('d-none');
        
        // Find the option marked with data-selected="true"
        let selectedOption = $('#book_translator').find('button[data-selected="true"]');
        
        if (selectedOption.length > 0) {
            // Set the value using Select2 API
            $('#book_translator').val(selectedOption.val()).trigger('change');
        } else {
          let unameOption = $(`[data-uname]`);
          let unameValue = unameOption.data('uname');

          if (unameValue) {
              // If data-uname is found, set it as the value
              $('#book_translator').val(unameValue).trigger('change');
          } else {
              console.error("No value found for data-uname attribute.");
              // Optionally, handle the case when no value is found
          }
        }
        // Destroy Select2 to disable interaction and apply custom styles
        $('#book_translator').css('pointer-events', 'none').css('background-color', '#e9ecef');
        
    } else {
        // Hide the translator dropdown
        $('#book-translator-form-data').addClass('d-none');

        $('#book_translator').css('pointer-events', '').css('background-color', '');  // Reset styles
    }
  });
  
  if(window.location.href.match(/edit/)) {
    $('#is_book_author').trigger('change');
      // Prefill the old value into the input and hidden field
    if (oldBookAuthor && old_book_author_id) {
        $('#book_author').val(oldBookAuthor).attr('data-value', old_book_author_id);
        $(`[name="post_meta[book_author]"]`).val($('#book_author').data('value'));
    }

    $('#is_book_translator').trigger('change');
    if (oldBookTranslator && old_book_translator_id) {
        $('#book_translator').val(oldBookTranslator).attr('data-value', old_book_translator_id);
        $(`[name="post_meta[book_translator]"]`).val($('#book_translator').data('value'));
    }
    // $('#is_for_read').trigger('change');
    $(".media-audio-file-show").sortable({
      update: function(event, ui) {
          // When the order changes, log the new order
          var sortedIDs = $(this).sortable("toArray", {attribute: 'data-id'});
      }
    });

  }

  function is_for_read(field = $(`#is_for_read`)) {
    let value = field.is(`:checked`) ? "on" : "off";
    let form = field.parents('form');
    if(value == "on") {
      form.find('.off-d-none').removeClass('d-none');
      form.find('.on-d-none').addClass('d-none');
      form.find('.on-req > label').prepend(`
        <sup class="text-danger"> * </sup>
      `);
    } else {
      form.find('.off-d-none').addClass('d-none');
      form.find('.on-d-none').removeClass('d-none');
      form.find('.on-req > label > sup').remove();
    }
  }

  is_for_read(field = $(`#is_for_read`));

  $(`#is_for_read`).on('change', function() {
    is_for_read(field = $(`#is_for_read`));
  });

  // media modal auto select section

  $('#mediaUploader').on('shown.bs.modal', function (event) {
    setTimeout(function() {
      if($(`#is_book_author`).length === 1) {
        $('#mediaUploader').find(`#select-file-category`).val("1").trigger('change').css('pointer-events', 'none').css('background-color', '#e9ecef');
      }
    }, 100);
  });

  // audio

  $(`#book-download-btn`).off('click').on('click', function() {
    if($(this).hasClass("book-downloadable")) {
      $(`#downloadPdf`).modal('toggle');
    } else {
      swal({
        text: 'لا يمكن تحميل الكتاب',
        icon: "error",
        buttons: {
          حسنا: true,
        },
      });
    }
  });

  $(`#book-preview-btn`).unbind().on('click', function(e) {
    let btn = $(this)
    if(btn.hasClass("book-preview")) {
      $(`#bookReaderModal`).modal('toggle');
    } else {
	  e.stopPropagation();
      swal({
        text: 'لا يمكن معاينة الكتاب',
        icon: "error",
        buttons: {
          حسنا: true,
        },
      });
    }
  });


  // pdf reader
  if ($('#bookReaderModal').length > 0) {
    let pdfDoc = null;
    let currentPage = 1;
    let totalPages = 0;
    let zoomLevel = 1;
    let observer = null;
    let pdfRendered = false;
    const viewerContainer = document.getElementById('pdfViewerContainer');
    const viewer = document.getElementById('pdfViewer');
    const pagesRendered = new Set(); // Track rendered pages

    // Load PDF
    async function loadPDF(url) {
        const loadingTask = pdfjsLib.getDocument(url);
        pdfDoc = await loadingTask.promise;
        totalPages = pdfDoc.numPages;
        document.getElementById('totalPages').textContent = totalPages;
        
        viewer.innerHTML = '';  // Clear previous content
        pdfRendered = false;
        pagesRendered.clear();  // Reset rendered pages tracking

        // Set up the page placeholders (for lazy loading)
        setupPagePlaceholders();

        // Set up IntersectionObserver for lazy loading
        setupPageObserver();
        pdfRendered = true;  // Mark PDF as loaded
    }

    // Setup page placeholders (before rendering) for lazy loading
    function setupPagePlaceholders() {
        for (let i = 1; i <= totalPages; i++) {
            const pagePlaceholder = document.createElement('div');
            pagePlaceholder.className = 'pdf-page-placeholder';
            pagePlaceholder.setAttribute('data-page-number', i);
            pagePlaceholder.style.height = '100vh'; // Placeholder height for each page
            viewer.appendChild(pagePlaceholder);
        }
    }

    // Render a specific page when it enters the viewport
    async function renderPage(num) {
        if (pagesRendered.has(num)) return;  // Avoid re-rendering the same page
        const page = await pdfDoc.getPage(num);
        const viewport = page.getViewport({ scale: zoomLevel });

        const canvas = document.createElement('canvas');
        canvas.className = 'pdf-page';
        canvas.setAttribute('data-page-number', num);
        
        const pagePlaceholder = document.querySelector(`.pdf-page-placeholder[data-page-number="${num}"]`);
        if (pagePlaceholder) {
            pagePlaceholder.replaceWith(canvas);  // Replace placeholder with actual canvas
        }

        canvas.height = viewport.height;
        canvas.width = viewport.width;
        const renderContext = {
            canvasContext: canvas.getContext('2d'),
            viewport: viewport
        };

        await page.render(renderContext).promise;
        pagesRendered.add(num);  // Mark this page as rendered
    }

    // Set up IntersectionObserver for lazy loading
    function setupPageObserver() {
        const options = {
            root: viewerContainer,
            threshold: 0.1 // Trigger when 10% of the page is visible
        };

        if (observer) observer.disconnect();  // Clean up previous observers
        observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const pageNum = parseInt(entry.target.getAttribute('data-page-number'), 10);
                    renderPage(pageNum);  // Load and render the page when it enters the viewport
                }
            });
        }, options);

        document.querySelectorAll('.pdf-page-placeholder').forEach(page => observer.observe(page));
    }

    // Scroll to a specific page (manually triggered by the input)
    function scrollToPage(pageNumber) {
        const page = document.querySelector(`[data-page-number="${pageNumber}"]`);
        if (page) {
            const yOffset = page.offsetTop;
            viewerContainer.scrollTo({ top: yOffset, behavior: 'smooth' });
        }
    }

    // Listen for manual page input
    document.getElementById('currentPage').addEventListener('change', function() {
        let pageNumber = parseInt(this.value);
        if (pageNumber >= 1 && pageNumber <= totalPages) {
            currentPage = pageNumber;
            scrollToPage(currentPage);  // Scroll to the specified page
            updateProgressBar();        // Update the progress bar
        }
    });

    // Function to update the progress bar width
    function updateProgressBar() {
        if (pdfRendered) {
            const progress = (currentPage / totalPages) * 100;
            document.getElementById('progressBar').style.width = `${progress}%`;
        }
    }

    // Scroll event handler (debounced)
    function debounce(func, wait) {
        let timeout;
        return function () {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    // Automatically update currentPage as user scrolls (debounced version)
    viewerContainer.addEventListener('scroll', debounce(() => {
        if (!pdfRendered) return;  // Don't update progress if PDF isn't rendered
        
        const pages = document.querySelectorAll('.pdf-page');
        let closestPage = null;
        let closestDistance = Infinity;

        // Find the page that's closest to the top of the viewport
        pages.forEach(page => {
            const rect = page.getBoundingClientRect();
            const distance = Math.abs(rect.top);

            if (distance < closestDistance) {
                closestDistance = distance;
                closestPage = page;
            }
        });

        if (closestPage) {
            const pageNum = parseInt(closestPage.getAttribute('data-page-number'), 10);
            if (pageNum !== currentPage) {
                currentPage = pageNum;
                document.getElementById('currentPage').value = currentPage;  // Update the input field
                updateProgressBar();  // Update the progress bar
            }
        }
    }, 150)); // Debounce for 150ms

    // Zoom controls
    document.getElementById('zoomRange').addEventListener('input', function () {
        zoomLevel = this.value / 100;
        viewer.innerHTML = '';  // Clear the viewer and re-render
        pagesRendered.clear();  // Clear rendered page tracking
        setupPagePlaceholders(); // Recreate placeholders for lazy loading
        setupPageObserver();    // Reapply the page observer after re-rendering
    });

    // Load PDF on modal open
    document.getElementById('bookPartSelect').addEventListener('change', function () {
        const selectedPdfUrl = this.value;
        loadPDF(selectedPdfUrl);  // Load the selected PDF part
    });

    document.getElementById('zoomBtn').addEventListener('click', function() {
      $(this.nextElementSibling).toggleClass('d-none');
    });

    function updatePreview(btnTarget) {
      let uid = btnTarget.data("uid"),
          pid = btnTarget.data("pid");
      $.ajax({
        url: `user-ajax.php`,
        type: "POST",
        data: {action: 'updatereview', user_id: uid, post_id: pid},
        success: function(response) {
          if(parseInt(response) > 0) {
            btnTarget.find('span.count').text(response)
          }
        }
      })
    }

    $('#bookReaderModal').on('shown.bs.modal', function (e) {
        let btnTarget = $(`[data-target="#bookReaderModal"]`);
        loadPDF(document.getElementById('bookPartSelect').value);
        setTimeout(function() {
          updatePreview(btnTarget)
        });
    });
  }

  // summary books
  $(`#book-summary-btn`).unbind().on('click', function() {
    $.ajax({
      url: `${gbj.siteurl}/admin/admin-ajax.php`,
      type: 'POST',
      data: {action: 'summarybook'},
      success: function(response) {
        console.log(response);
        // $(`#fetchDetailsModal .modal-body .table tbody`).html(``);
        // let datas = response ? JSON.parse(response) : [];
        // if(datas.length > 0) {
        //   for(let row of datas) {
        //     $(`#fetchDetailsModal .modal-body .table tbody`).append(`
        //       <tr>
        //         <td>${row.date}</td>
        //         <td>${row.status.success}</td>
        //         <td>${row.status.fails}</td>
        //         <td>${row.status.exists}</td>
        //       </tr>
        //     `);
        //   };
        // } else {
        //   $(`#fetchDetailsModal .modal-body .table tbody`).append(`
        //     <tr>
        //       <td colspan="4" class="text-center">لا توجد بيانات</td>
        //     </tr>
        //   `);
        // }
      }
    });
  });

  // download books

  if($('#downloadPdf').length > 0) {
    $('#downloadPdf').on('shown.bs.modal', function (event) {
      let totalSize = 0;
      let selectedBooks = [];
  
      // Event handler for checkbox change
      $('input[name="books_download"]').on('change', function () {
          const bookId = $(this).data('id');
          const fileSize = parseFloat($(this).closest('a').find('span:last-child').text()); // Get file size from the element
  
          if (this.checked) {
              // Add book size to total and append bookId to selectedBooks array
              totalSize += fileSize;
              selectedBooks.push(bookId);
          } else {
              // Subtract book size from total and remove bookId from selectedBooks array
              totalSize -= fileSize;
              selectedBooks = selectedBooks.filter(id => id !== bookId);
          }
  
          // Update total size in the download button
          $('.counter').text(totalSize.toFixed(2));
  
          // Update the hidden input field with selected book IDs
          $('input[name="books[file_id]"]').val(selectedBooks.join(','));
      });
  
  
      $('#total-download-btn').on('click', function (e) {
        e.preventDefault(); // Prevent default anchor behavior
        let btn = $(this);
        const selectedBooks = $('input[name="books[file_id]"]').val().split(',');
    
        if (selectedBooks.length === 0 || selectedBooks[0] === "") {
            alert('Please select at least one file to download.');
            return;
        }
    
        // Create a form dynamically to submit selected files as a POST request
        const form = $('<form></form>').attr('method', 'POST').attr('action', btn.attr('href'));
        
        selectedBooks.forEach(bookId => {
            form.append($('<input>').attr('type', 'hidden').attr('name', 'books_ids[]').val(bookId));
        });
    
        $('body').append(form);
        form.submit();
      });
  
  
    });
  }


  $("#author-account").select2({
    tags: true,
    dir: "rtl",
    width: '100%',
    language: 'ar',
    dropdownAutoWidth: true,
    dropdownParent: $('#author-account-select2')
  });

});