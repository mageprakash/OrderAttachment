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
                template: 'Sp_Orderattachment/attachment-markup'
            },
            attachmentList: ko.observableArray([]),

            initialize: function () {

                this._super();
                var self = this;
                this.attachementData = this.dataconfig;
                var quote = this.attachementData.quoteData;
                this.allowedExtensions = this.attachementData.spAttachmentExt;
                this.maxFileSize = this.attachementData.spAttachmentSize;
                this.removeItem = this.attachementData.removeItem;
                this.maxFileLimit = this.attachementData.spAttachmentLimit;
                this.invalidExtError = this.attachementData.spAttachmentInvalidExt;
                this.invalidSizeError = this.attachementData.spAttachmentInvalidSize;
                this.invalidLimitError = this.attachementData.spAttachmentInvalidLimit;
                this.uploadUrl = this.attachementData.spAttachmentUpload;
                this.updateUrl = this.attachementData.spAttachmentUpdate;
                this.removeUrl = this.attachementData.spAttachmentRemove;
                this.comment = this.attachementData.spAttachmentComment;
                this.attachments = this.attachementData.attachments;
                this.attachmentTitle = this.attachementData.spAttachmentTitle;
                this.attachmentInfromation = this.attachementData.spAttachmentInfromation;

                this.attachmentList(this.attachments);
                this.files = this.attachementData.totalCount;

            },
            showRowLoader: function() {
                $('body').trigger('processStart');
            },

            hideRowLoader: function() {
               $('body').trigger('processStop');
            },

            processingFile: function(file) {
                var error = this.validateFile(file);
                if (error) {
                    this.addError(error);
                } else {

                    if (this.files >= this.maxFileLimit) {
                        this.addError(this.invalidLimitError);
                    } else {
                        var uniq = Math.random().toString(32).slice(2);
                        this.upload(file, uniq);
                    }
                }
            },

            upload: function(file, pos) {
                var formAttach = new FormData(),
                self = this;

                this.showRowLoader();
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
                        self.attachments.push(result);
                        self.attachmentList(self.attachments);
                        if(result['attachment_count']){
                            self.files = result['attachment_count'];
                        }
                        self.hideRowLoader();
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        self.addError(thrownError);
                        self.hideRowLoader();
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            },

            updateComment: function(id, hash,commentElement) {

                var comment = $('#'+commentElement).val();
                
                if($.trim(comment))
                {
                    var attachParams = {
                        'attachment': id,
                        'hash': hash,
                        'comment': comment,
                        'form_key': window.FORM_KEY
                    },
                    self = this;
                    this.showRowLoader();

                    $.ajax({
                        url: this.updateUrl,
                        type: "post",
                        data: $.param(attachParams),
                        success: function(data) {
                            var result = JSON.parse(data);
                            if (!result.success) {
                                self.addError(result.error);
                            }else{
                                if(result['attachment_count']){
                                    self.files = result['attachment_count'];
                                }
                            }
                            self.hideRowLoader();
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            self.addError(thrownError);
                            self.hideRowLoader();
                        }
                    });
                }
            },
            deleteFile: function(id, hash) {
                    
                var attachParams = {
                    'attachment': id,
                    'hash': hash,
                    'form_key': window.FORM_KEY
                },
                self = this;

                self.showRowLoader();

                $.ajax({
                    url: this.removeUrl,
                    type: "post",
                    data: $.param(attachParams),
                    success: function(data) {
                        var result = JSON.parse(data);
                        if (result.success) {
                            if(result['attachment_count'])
                            {
                                self.files = result['attachment_count'];
                            }
                            $('div.sp-attachment-row[rel="' + hash + '"]').remove();
                        }
                        self.hideRowLoader();
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        self.addError(thrownError);
                        self.hideRowLoader();
                    }
                });
            },

            downloadFile: function () {
                window.location = this.download;
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
            }

        });
    }
);
