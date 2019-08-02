define(
    [
        'jquery',
        'ko',
        'uiComponent'
    ],
    function ($, ko, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Sp_Orderattachment/order/shipment/attachment-markup'
            },
            isVisible: window.checkoutConfig.spAttachmentEnabled,
            initialize: function () {
                this._super();
                this.files = {};
                var quote = window.checkoutConfig.quoteData;
                this.allowedExtensions = window.checkoutConfig.spAttachmentExt;
                this.maxFileSize = window.checkoutConfig.spAttachmentSize;
                this.removeItem = window.checkoutConfig.removeItem;
                this.maxFileLimit = window.checkoutConfig.spAttachmentLimit;
                this.invalidExtError = window.checkoutConfig.spAttachmentInvalidExt;
                this.invalidSizeError = window.checkoutConfig.spAttachmentInvalidSize;
                this.invalidLimitError = window.checkoutConfig.spAttachmentInvalidLimit;
                this.uploadUrl = window.checkoutConfig.spAttachmentUpload;
                this.updateUrl = window.checkoutConfig.spAttachmentUpdate;
                this.removeUrl = window.checkoutConfig.spAttachmentRemove;
                this.comment = window.checkoutConfig.spAttachmentComment;
                this.attachments = window.checkoutConfig.attachments;
                this.attachmentTitle = window.checkoutConfig.spAttachmentTitle;
                this.attachmentInfromation = window.checkoutConfig.spAttachmentInfromation;
                var self = this;
                setTimeout(function() {
                    self.addUploadedItems();
                    self.prepareObservers();
                }, 1000);
            },

            getTitle: function() {
                return this.attachmentTitle;
            },

            getAttachmentInfo: function() {
                return this.attachmentInfromation;
            },

            selectFiles: function() {
                $('#order-attachment').trigger('click');
            },

            fileUpload: function(data, e) {                
                var file    = e.target.files;
                console.log(file);
                for (var i = 0; i < file.length; i++) {
                    this.processingFile(file[i]);
                }
            },

            dragEnter: function(data, event) {},

            dragOver: function(data, event) {},

            drop: function(data, event) {
                $('.order-attachment-drag-area').css("border", "2px dashed #1979c3");
                var droppedFiles = event.originalEvent.dataTransfer.files;
                console.log(droppedFiles);
                for (var i = 0; i < droppedFiles.length; i++) {
                    this.processingFile(droppedFiles[i]);
                }
            },

            prepareObservers: function() {
              /*  var self = this;
                $(document).on("dragenter", function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                });
                $(document).on("dragover", function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                });
                $(document).on("drop", function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                });

                $('#order-attachment').on('change', function(event) {
                    $.each(this.files, function(key, file) {
                        self.processingFile(file);
                    });
                });*/
            },

            addUploadedItems: function() {
                var attachments = this.attachments,
                    self = this;
                if (attachments) {
                    for (var i in attachments) {
                        if (!attachments.hasOwnProperty(i)) {
                            continue;
                        }
                        var attachment = attachments[i];
                        var uniq = Math.random().toString(32).slice(2);
                        self.files[uniq] = attachment.path;
                        self.addAttachmentMarkup(uniq, attachment.path);
                        self.addAttachmentContent(uniq, attachment);
                    }
                }
            },

            addAttachmentMarkup: function(pos, fileName) {
                var container = $('.attachment-container'),
                    newRow = $('<div class="sp-attachment-row" rel="' + pos + '"></div>').appendTo(container),
                    loader = $('<div class="sp-attachment-loader"><div class="circle"></div><div class="circle"></div><div class="circle"></div></div>').appendTo(newRow),
                    rowContent = $('<div class="sp-attachment-row-content"></div>').appendTo(newRow),
                    preview = $('<div class="order-attachment-preview"></div>').appendTo(rowContent);
                $('<div class="order-attachment-content"></div>').appendTo(rowContent);
                var finfo = $('<div class="attachment-file"></div>').appendTo(preview);
                finfo.append('<div class="filename">' + fileName + "</div>");
            },

            addAttachmentContent: function(pos, attachment) {
                var self = this,
                    row = $('div.sp-attachment-row[rel="' + pos + '"]'),
                    preview = row.find(".order-attachment-preview");
                this.previewFile(preview, attachment.type, attachment.preview);
                var content = row.find(".order-attachment-content"),
                    attachId = attachment.attachment_id;
                var html = '<textarea id="attachment-comment'+attachId+'" rows="4" name="attachment['+
                            attachId+'][comment]" class="comment" placeholder="'+this.comment+'">'+attachment.comment+'</textarea>' +
                            '<a id="sp-attachment-remove'+attachId+'" class="sp-attachment-remove" title="'+this.removeItem+'" href="#"></a>'+
                            '<input type="hidden" class="sp-attachment-id'+attachId+'" name="attachment-id" value="'+attachId+'">' +
                            '<input type="hidden" class="sp-attachment-hash'+attachId+'" name="attachment-hash" value="'+attachment.hash+'">';
                $(html).appendTo(content);
                this.hideRowLoader(row);
                var id = row.find('.sp-attachment-id' + attachId).val(),
                    hash = row.find('.sp-attachment-hash' + attachId).val();
                $('#attachment-comment' + attachId).focusout(function() {
                    if ($(this).val()) {
                        self.updateComment(id, hash, $(this).val(), pos);
                    }
                });
                $('#sp-attachment-remove' + attachId).on('click', function(event) {
                    event.preventDefault();
                    self.removeFile(id, hash, pos);
                });
            },

            showRowLoader: function(row) {
                jQuery('body').loader('show');
            },

            hideRowLoader: function(row) {
                jQuery('body').loader('hide');
            },

            processingFile: function(file) {
                var error = this.validateFile(file);
                if (error) {
                    this.addError(error);
                } else {
                    var filesLen = Object.keys(this.files).length;
                    if (Object.keys(this.files).length >= this.maxFileLimit) {
                        this.addError(this.invalidLimitError);
                    } else {
                        var uniq = Math.random().toString(32).slice(2);
                        this.files[uniq] = file.name;
                        this.addAttachmentMarkup(uniq, file.name);
                        this.upload(file, uniq);
                    }
                }
            },

            upload: function(file, pos) {
                var formAttach = new FormData(),
                    self = this,
                    row = $('div.sp-attachment-row[rel="' + pos + '"]');
                this.showRowLoader(row);
                formAttach.append($('#order-attachment').attr("name"), file);
                if (window.FORM_KEY) {
                    formAttach.append('form_key', window.FORM_KEY);
                }
                $.ajax({
                    url: this.uploadUrl,
                    type: "POST",
                    data: formAttach,
                    success: function(data) {
                        var result = JSON.parse(data);
                        if (result.success) {
                            self.addAttachmentContent(pos, result);
                        } else {
                            self.addError(result.error);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        self.addError(thrownError);
                        delete this.files[pos];
                        $('div.sp-attachment-row[rel="' + pos + '"]').remove();
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            },

            updateComment: function(id, hash, comment, pos) {
                var attachParams = {
                    'attachment': id,
                    'hash': hash,
                    'comment': comment,
                    'form_key': window.FORM_KEY
                },
                    self = this,
                    row = $('div.sp-attachment-row[rel="' + pos + '"]');
                    this.showRowLoader(row);
                $.ajax({
                    url: this.updateUrl,
                    type: "post",
                    data: $.param(attachParams),
                    success: function(data) {
                        var result = JSON.parse(data);
                        if (!result.success) {
                            self.addError(result.error);
                        }
                        self.hideRowLoader(row);
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        self.addError(thrownError);
                    }
                });

            },

            removeFile: function(id, hash, pos) {
                var attachParams = {
                    'attachment': id,
                    'hash': hash,
                    'form_key': window.FORM_KEY
                },
                    self = this,
                    row = $('div.sp-attachment-row[rel="' + pos + '"]');
                    this.showRowLoader(row);
                $.ajax({
                    url: this.removeUrl,
                    type: "post",
                    data: $.param(attachParams),
                    success: function(data) {
                        var result = JSON.parse(data);
                        if (result.success) {
                            delete self.files[pos];
                            row.fadeOut("500", function() {
                                $(this).remove();
                            });
                        }
                        self.hideRowLoader(row);
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        self.addError(thrownError);
                        self.hideRowLoader(row);
                    }
                });
            },

            previewFile: function(container, attachType, fileUrl) {
                var type = attachType.split("/")[0],
                    c = container.find('.attachment-file'),
                    prev = '';
                switch (type) {
                    case "image":
                        prev = $('<img class="thumbnail" src="' + fileUrl + '" />').insertBefore(c);
                    break;

                    case "video":
                        prev = $('<video src="' + fileUrl + '" width="100%" controls></video>').insertBefore(c);
                    break;

                    case "audio":
                        prev = $('<audio src="' + fileUrl + '" style="display:block; width:100%;" controls></audio>').insertBefore(c);
                      break;

                    default:
                        prev = $('<div class="sp-attachment-default-preview"></div>').insertBefore(c);
                      break;
                }
            },

            validateFile: function(file) {
                if (!this.checkFileExtension(file)) {
                    return this.invalidExtError;
                }
                if (!this.checkFileSize(file)) {
                    return this.invalidSizeError;
                }

                return null;
            },

            checkFileExtension: function(file) {
                var fileExt = file.name.split(".").pop().toLowerCase();
                var allowedExt = this.allowedExtensions.split(",");
                if (-1 == $.inArray(fileExt, allowedExt)) {
                    return false;
                }
                return true;
            },

            checkFileSize: function(file) {
                if ((file.size / 1024) > this.maxFileSize) {
                    return false;
                }
                return true;
            },

            addError: function(error) {
                var html = null;
                html = '<div class="sp-attachment-error danger"><strong class="close">X</strong>'+ error +'</div>';
                $('.attachment-container').before(html);
                $(".sp-attachment-error .close").on('click', function() {
                    var el = $(this).closest("div");
                    if (el.hasClass('sp-attachment-error')) {
                        $(el).slideUp('slow', function() {
                            $(this).remove();
                        });
                    }
                });
            }
        });
    }
);
