(function ($) {

    if (window.H5PEditor === undefined) {
        return;
    }

    function getUrlParamValues(params) {
        var urlParams = new URLSearchParams(window.location.search);
        if (!params || params.length == 0) {
            return '';
        }

        if (Array.isArray(params)) {
            var returnArray = [];
            for (const param of params) {
                var paramValue = urlParams.get(param);
                returnArray.push(paramValue);
            }
            return returnArray;
        } else {
            return urlParams.get(params);
        }        
    }

    function getOpencastActionUrl(action) {
        var contextid = getUrlParamValues('contextid');
        
        var ocAjaxPath = H5PEditor.ajaxPath.replace('/h5p', '/local/och5pcore');

        var OCActionUrl = ocAjaxPath + action;
        if (contextid) {
            OCActionUrl += '&contextid=' + contextid;
        }

        return OCActionUrl;
    }

    function opencastAjaxCallSync(action, instance = null) {
        if (!action) {
            return;
        }

        let result = null;
        $.ajax({
            url: getOpencastActionUrl(action),
            success: (data) => {
                if (data.error) {
                    if (instance) {
                        instance.$addDialog.find('.h5p-oc-search-error').text(data.error).show();
                    } else {
                        console.log(data.error);
                    }
                    return;
                }
                result = data.result;
            },
            error: (request, status, error) => {
                if (instance) {
                    instance.$addDialog.find('.h5p-oc-search-error').text(request.responseText).show();
                } else {
                    console.log(request.responseText);
                }
            },
            async: false
        });

        return result;
    }
    
    function getCourseVideosInsertDropdown() {
        var action = getUrlParamValues('contextid') == 1 ? 'courseList' : 'courseVideos';
        var dropdownContent = opencastAjaxCallSync(action);
        if (!dropdownContent) {
            return '';
        }

        var dropdownDisplay = '';
        if (action == 'courseVideos') {
            dropdownDisplay = '<div class="field select">' +
                '<select id="h5p-oc-course-videos" class="h5peditor-select h5p-oc-videos-selection">' + dropdownContent + '</select>' +
            '</div>';
        } else {
            dropdownDisplay = '<div class="field select">' +
                '<select id="h5p-oc-course-list" class="h5peditor-select h5p-oc-videos-selection">' + dropdownContent + '</select>' +
            '</div>' +
            '<div class="field select" style="display: none;">' +
                '<select id="h5p-oc-course-videos" class="h5peditor-select h5p-oc-videos-selection"></select>' +
            '</div>';
        }

        return  '<div class="h5p-oc-horizontal-separator"></div>' +
                '<div class="h5p-add-dialog-table">' +
                    '<div class="h5p-oc-dialog-box">' +
                        '<h3>Opencast Videos</h3>' + 
                        '<div class="h5p-oc-video-wrapper">' +
                            dropdownDisplay +
                            '<div class="field select" style="display: none;">' + 
                                '<select id="h5p-oc-video-quality" class="h5peditor-select h5p-oc-videos-quality"></select>' +
                            '</div>' +
                        '</div>' +
                        '<div class="h5p-oc-search-error"></div>' + 
                    '</div>'+
                '</div>';
        
       
    }

    H5PEditor.AV.createAdd = function (type, id, hasDescription) {
        var InsertContent = 
        '<div class="h5p-dialog-box">' +
        H5PEditor.AV.createTabContent('BasicFileUpload', type) +
        '</div>' +
        '<div class="h5p-or-vertical">' +
        '<div class="h5p-or-vertical-line"></div>' +
        '<div class="h5p-or-vertical-word-wrapper">' +
        '<div class="h5p-or-vertical-word">' + H5PEditor.t('core', 'or') + '</div>' +
        '</div>' +
        '</div>' +
        '<div class="h5p-dialog-box">' +
        H5PEditor.AV.createTabContent('InputLinkURL', type) +
        '</div>';
        var OCcontent = ( type !== 'audio' && getCourseVideosInsertDropdown() ? getCourseVideosInsertDropdown() : '');
        return H5PEditor.AV.createInsertDialog(InsertContent, false, id, hasDescription, OCcontent);
    };

    H5PEditor.AV.createInsertDialog = function (content, disableInsert, id, hasDescription, OCcontent = '') {
        return '<div role="button" tabindex="0" id="' + id + '"' + (hasDescription ? ' aria-describedby="' + ns.getDescriptionId(id) + '"' : '') + ' class="h5p-add-file" title="' + H5PEditor.t('core', 'addFile') + '"></div>' +
        '<div class="h5p-dialog-anchor"><div class="h5p-add-dialog">' +
        '<div class="h5p-add-dialog-table">' + content + '</div>' +
        OCcontent +
        '<div class="h5p-buttons">' +
        '<button class="h5peditor-button-textual h5p-insert"' + (disableInsert ? ' disabled' : '') + '>' + H5PEditor.t('core', 'insert') + '</button>' +
        '<button class="h5peditor-button-textual h5p-cancel">' + H5PEditor.t('core', 'cancel') + '</button>' +
        '</div>' +
        '</div></div>';
    };
    
    /**
    * Append widget to given wrapper.
    *
    * @param {jQuery} $wrapper
    */
    H5PEditor.AV.prototype.appendTo = function ($wrapper) {
        var self = this;
        const id = ns.getNextFieldId(this.field);
        
        var imageHtml =
        '<ul class="file list-unstyled"></ul>' +
        (self.field.widgetExtensions ? H5PEditor.AV.createTabbedAdd(self.field.type, self.field.widgetExtensions, id, self.field.description !== undefined) : H5PEditor.AV.createAdd(self.field.type, id, self.field.description !== undefined))
        
        if (!this.field.disableCopyright) {
            imageHtml += '<a class="h5p-copyright-button" href="#">' + H5PEditor.t('core', 'editCopyright') + '</a>';
        }
        
        imageHtml += '<div class="h5p-editor-dialog">' +
        '<a href="#" class="h5p-close" title="' + H5PEditor.t('core', 'close') + '"></a>' +
        '</div>';
        
        var html = H5PEditor.createFieldMarkup(this.field, imageHtml, id);
        var $container = $(html).appendTo($wrapper);
        
        this.$files = $container.children('.file');
        this.$add = $container.children('.h5p-add-file').click(function () {
            self.$addDialog.addClass('h5p-open');
        });
        
        // Tabs that are hard-coded into this widget. Any other tab must be an extension.
        const TABS = {
            UPLOAD: 0,
            INPUT: 1
        };
        
        // The current active tab
        let activeTab = TABS.UPLOAD;
        
        /**
        * @param {number} tab
        * @return {boolean}
        */
        const isExtension = function (tab) {
            return tab > TABS.INPUT; // Always last tab
        };
        
        /**
        * Toggle the currently active tab.
        */
        const toggleTab = function () {
            // Pause the last active tab
            if (isExtension(activeTab)) {
                tabInstances[activeTab].pause();
            }
            
            // Update tab
            this.parentElement.querySelector('.selected').classList.remove('selected');
            this.classList.add('selected');
            
            // Update tab panel
            const el = document.getElementById(this.getAttribute('aria-controls'));
            el.parentElement.querySelector('.av-tabpanel:not([hidden])').setAttribute('hidden', '');
            el.removeAttribute('hidden');
            
            // Set active tab index
            for (let i = 0; i < el.parentElement.children.length; i++) {
                if (el.parentElement.children[i] === el) {
                    activeTab = i - 1; // Compensate for .av-tablist in the same wrapper
                    break;
                }
            }
            
            // Toggle insert button disabled
            if (activeTab === TABS.UPLOAD) {
                self.$insertButton[0].disabled = true;
            }
            else if (activeTab === TABS.INPUT) {
                self.$insertButton[0].disabled = false;
            }
            else {
                self.$insertButton[0].disabled = !tabInstances[activeTab].hasMedia();
            }
        }
        
        /**
        * Switch focus between the buttons in the tablist
        */
        const moveFocus = function (el) {
            if (el) {
                this.setAttribute('tabindex', '-1');
                el.setAttribute('tabindex', '0');
                el.focus();
            }
        }
        
        // Register event listeners to tab DOM elements
        $container.find('.av-tab').click(toggleTab).keydown(function (e) {
            if (e.which === 13 || e.which === 32) { // Enter or Space
                toggleTab.call(this, e);
                e.preventDefault();
            }
            else if (e.which === 37 || e.which === 38) { // Left or Up
                moveFocus.call(this, this.previousSibling);
                e.preventDefault();
            }
            else if (e.which === 39 || e.which === 40) { // Right or Down
                moveFocus.call(this, this.nextSibling);
                e.preventDefault();
            }
        });
        
        this.$addDialog = this.$add.next().children().first();
        
        // Prepare to add the extra tab instances
        const tabInstances = [null, null]; // Add nulls for hard-coded tabs
        self.tabInstances = tabInstances;
        
        if (self.field.widgetExtensions) {
            
            /**
            * @param {string} type Constructor name scoped inside this widget
            * @param {number} index
            */
            const createTabInstance = function (type, index) {
                const tabInstance = new H5PEditor.AV[type]();
                tabInstance.appendTo(self.$addDialog[0].children[0].children[index + 1]); // Compensate for .av-tablist in the same wrapper
                tabInstance.on('hasMedia', function (e) {
                    if (index === activeTab) {
                        self.$insertButton[0].disabled = !e.data;
                    }
                });
                tabInstances.push(tabInstance);
            }
            
            // Append extra tabs
            for (let i = 0; i < self.field.widgetExtensions.length; i++) {
                if (H5PEditor.AV[self.field.widgetExtensions[i]]) {
                    createTabInstance(self.field.widgetExtensions[i], i + 2); // Compensate for the number of hard-coded tabs
                }
            }
        }
        
        var $url = this.$url = this.$addDialog.find('.h5p-file-url');
        this.$addDialog.find('.h5p-cancel').click(function () {
            self.updateIndex = undefined;
            self.closeDialog();
        });
        
        this.$addDialog.find('.h5p-file-drop-upload')
        .addClass('has-advanced-upload')
        .on('drag dragstart dragend dragover dragenter dragleave drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
        })
        .on('dragover dragenter', function (e) {
            $(this).addClass('over');
            e.originalEvent.dataTransfer.dropEffect = 'copy';
        })
        .on('dragleave', function () {
            $(this).removeClass('over');
        })
        .on('drop', function (e) {
            if ($(this).hasClass('disabled')) {
                return;
            }
            self.uploadFiles(e.originalEvent.dataTransfer.files);
        })
        .click(function () {
            if ($(this).hasClass('disabled')) {
                return;
            }
            self.openFileSelector();
        });

        var $oc_video_wrapper = this.$addDialog.find('.h5p-oc-dialog-box > .h5p-oc-video-wrapper');
        var $oc_course_list = $oc_video_wrapper.find('#h5p-oc-course-list');
        var $oc_video = $oc_video_wrapper.find('#h5p-oc-course-videos');
        var $oc_video_quality = $oc_video_wrapper.find('#h5p-oc-video-quality');

        this.$ocCourseListSelect = $oc_course_list.change(function (e) {
            e.preventDefault();
            var courseid = $(this).val();
            self.$addDialog.find('.h5p-oc-search-error').text('').hide();
            $oc_video.empty();
            $oc_video.parent().hide();
            $oc_video_quality.empty();
            $oc_video_quality.parent().hide();
            self.$addDialog.find('.h5p-file-drop-upload').removeClass('disabled');
            self.$addDialog.find('.h5p-file-url').removeClass('disabled').removeAttr('disabled');
            if (courseid) {
                var courseVideos = opencastAjaxCallSync('courseVideos&courseid=' + courseid, self);
                if (courseVideos) {
                    $oc_video.empty();
                    courseVideos.forEach(function (option, index) {
                        $oc_video.append(option);
                    });

                    $oc_video.parent().show();
                }
                self.$addDialog.find('.h5p-file-drop-upload').addClass('disabled');
                self.$addDialog.find('.h5p-file-url').addClass('disabled').attr('disabled','disabled');
            }
        });

        this.$ocVideoSelect = $oc_video.change(function (e) {
            e.preventDefault();
            var identifier = $(this).val();
            self.$addDialog.find('.h5p-oc-search-error').text('').hide();
            $oc_video_quality.empty();
            $oc_video_quality.parent().hide();
            self.$addDialog.find('.h5p-file-drop-upload').removeClass('disabled');
            self.$addDialog.find('.h5p-file-url').removeClass('disabled').removeAttr('disabled');
            if (identifier) {
                var qualityOptions = opencastAjaxCallSync('videoQualities&identifier=' + identifier, self);
                if (qualityOptions) {
                    $oc_video_quality.empty();
                    qualityOptions.forEach(function (option, index) {
                        $oc_video_quality.append(option);
                    });

                    var predefinedQuality = $(this).data('predefinedQuality');
                    if (predefinedQuality) {
                        $oc_video_quality.val(predefinedQuality);
                        $(this).data('predefinedQuality', '');
                    }

                    $oc_video_quality.parent().show();
                }
                self.$addDialog.find('.h5p-file-drop-upload').addClass('disabled');
                self.$addDialog.find('.h5p-file-url').addClass('disabled').attr('disabled','disabled');
            }
        });

        this.$ocVideoQualitySelect = $oc_video_quality.change(function (e) {
            e.preventDefault();
            var ids = $(this).val().trim();
            var data = $(this).find(':selected').data('info');
            if (ids && data && data.qualities) {
                data.qualities.forEach(function (video_obj, index) {
                    self.useUrl(ids, video_obj);
                });
            }
            self.closeDialog();
        });

        this.$insertButton = this.$addDialog.find('.h5p-insert').click(function () {
            if (isExtension(activeTab)) {
                const media = tabInstances[activeTab].getMedia();
                if (media) {
                    self.upload(media.data, media.name);
                }
            }
            else {
                const url = $url.val().trim();
                if (url) {
                    self.useUrl(url);
                }
            }
            
            self.closeDialog();
        });
        
        
        this.$errors = $container.children('.h5p-errors');
        
        if (this.params !== undefined) {
            for (var i = 0; i < this.params.length; i++) {
                this.addFile(i);
            }
        }
        else {
            $container.find('.h5p-copyright-button').addClass('hidden');
        }
        
        var $dialog = $container.find('.h5p-editor-dialog');
        $container.find('.h5p-copyright-button').add($dialog.find('.h5p-close')).click(function () {
            $dialog.toggleClass('h5p-open');
            return false;
        });
        
        ns.File.addCopyright(self, $dialog, function (field, value) {
            self.setCopyright(value);
        });
        
    };

    /**
     * Close the add media dialog
     */
    H5PEditor.AV.prototype.closeDialog = function () {
        this.$addDialog.removeClass('h5p-open');

        // Reset URL input
        this.$url.val('');

        // Reset Opencast Selections
        this.$ocVideoSelect.val('');
        this.$ocVideoSelect.trigger('change');

        // Reset all of the tabs
        for (let i = 0; i < this.tabInstances.length; i++) {
            if (this.tabInstances[i]) {
                this.tabInstances[i].reset();
            }
        }
    };


    //OUR FIRST NEEDED ENDPOINT
    H5PEditor.AV.prototype.useUrl = function (url, opencastData = {}) {
        if (this.params === undefined) {
            this.params = [];
            this.setValue(this.field, this.params);
        }

        var mime;
        var aspectRatio;
        var i;
        var matches = url.match(/\.(webm|mp4|ogv|m4a|mp3|ogg|oga|wav)/i);
        if (matches !== null) {
            mime = matches[matches.length - 1];
        }
        else {
            // Try to find a provider
            for (i = 0; i < H5PEditor.AV.providers.length; i++) {
                if (H5PEditor.AV.providers[i].regexp.test(url)) {
                    mime = H5PEditor.AV.providers[i].name;
                    aspectRatio = H5PEditor.AV.providers[i].aspectRatio;
                    break;
                }
            }
        }
        
        var file = {
            path: url,
            mime: this.field.type + '/' + (mime ? mime : 'unknown'),
            copyright: this.copyright,
            aspectRatio: aspectRatio ? aspectRatio : undefined,
        };

        if (Object.keys(opencastData).length > 0) {
            file.mime = opencastData.mime;
            file.aspectRatio = '16:9';
            file.path = opencastData.url;
            file.id = url;
            file.identifier = opencastData.identifier;
            file.org = 'opencast';
            file.metadata = {
                "qualityName": opencastData.quality
            }
        }

        var index = (this.updateIndex !== undefined ? this.updateIndex : this.params.length);
        this.params[index] = file;
        this.addFile(index);
        
        for (i = 0; i < this.changes.length; i++) {
            this.changes[i](file);
        }
    };

    /**
    * Add file icon with actions.
    *
    * @param {Number} index
    */
    H5PEditor.AV.prototype.addFile = function (index) {
        var that = this;
        var fileHtml;
        var file = this.params[index];
        var rowInputId = 'h5p-av-' + H5PEditor.AV.getNextId();
        var defaultQualityName = H5PEditor.t('core', 'videoQualityDefaultLabel', { ':index': index + 1 });
        var qualityName = (file.metadata && file.metadata.qualityName) ? file.metadata.qualityName : defaultQualityName;

        // Check if source is YouTube
        var youtubeRegex = H5PEditor.AV.providers.filter(function (provider) {
            return provider.name === 'YouTube';
        })[0].regexp;
        var isYoutube = file.path && file.path.match(youtubeRegex);
        
        var isOpencast = (file.id && file.org && file.org == 'opencast') ? true : false;

        if (!isOpencast) { //double check on edit part...
            that.$ocVideoSelect.children().each(function(index, option) {
                var oc_identifier = $(option).val();
                if (oc_identifier == '') {
                    return;
                }
                if (file.path && file.path.includes('/' + $(option).val() + '/')) {
                    isOpencast = true;
                }
            });
        }
        

        // Only allow single source if YouTube
        if (isYoutube) {
            // Remove all other files except this one
            that.$files.children().each(function (i) {
                if (i !== that.updateIndex) {
                    that.removeFileWithElement($(this));
                }
            });
            // Remove old element if updating
            that.$files.children().each(function () {
                $(this).remove();
            });
            // This is now the first and only file
            index = 0;
        }

        this.$add.toggleClass('hidden', !!isYoutube);
        // this.$add.toggleClass('hidden', !!isOpencast);
        
        // If updating remove and recreate element
        if (that.updateIndex !== undefined) {
            var $oldFile = this.$files.children(':eq(' + index + ')');
            $oldFile.remove();
            this.updateIndex = undefined;
        }
        
        // Create file with customizable quality if enabled and not youtube
        if ((this.field.enableCustomQualityLabel === true && !isYoutube) || isOpencast) {
            var thumbnail_mimetype = isOpencast ?  'Opencast' : file.mime.split('/')[1];
            fileHtml = '<li class="h5p-av-row">' +
            '<div class="h5p-thumbnail">' +
            '<div class="h5p-type" title="' + file.mime + '">' + thumbnail_mimetype + '</div>' +
            '<div role="button" tabindex="0" class="h5p-remove" title="' + H5PEditor.t('core', 'removeFile') + '">' +
            '</div>' +
            '</div>' +
            '<div class="h5p-video-quality">' +
            '<div class="h5p-video-quality-title">' + H5PEditor.t('core', 'videoQuality') + '</div>' +
            '<label class="h5peditor-field-description" for="' + rowInputId + '">' + H5PEditor.t('core', 'videoQualityDescription') + '</label>' +
            '<input id="' + rowInputId + '" class="h5peditor-text" type="text" maxlength="60" value="' + qualityName + '">' +
            '</div>' +
            '</li>';
        }
        else {
            fileHtml = '<li class="h5p-av-cell">' +
            '<div class="h5p-thumbnail">' +
            '<div class="h5p-type" title="' + file.mime + '">' + file.mime.split('/')[1] + '</div>' +
            '<div role="button" tabindex="0" class="h5p-remove" title="' + H5PEditor.t('core', 'removeFile') + '">' +
            '</div>' +
            '</li>';
        }
        
        // Insert file element in appropriate order
        var $file = $(fileHtml);
        if (index >= that.$files.children().length) {
            $file.appendTo(that.$files);
        }
        else {
            $file.insertBefore(that.$files.children().eq(index));
        }
        
        this.$add.parent().find('.h5p-copyright-button').removeClass('hidden');
        
        // Handle thumbnail click
        $file
        .children('.h5p-thumbnail')
        .click(function () {
            if (!that.$add.is(':visible')) {
                return; // Do not allow editing of file while uploading
            }
            if (isOpencast) {

                /* It will create confusion since several qualities will be added to the file list automatically
                    therefore, it will be prevented to edit opencast videos 
                */
               return;
                // that.$addDialog.addClass('h5p-open');
                // that.$ocVideoSelect.val(that.params[index].identifier);
                // that.$ocVideoSelect.data('predefinedQuality', that.params[index].id);
                // that.$ocVideoSelect.trigger('change');
            } else {
                that.$addDialog.addClass('h5p-open').find('.h5p-file-url').val(that.params[index].path);
            }
            that.updateIndex = index;
        });
        
        // Handle remove button click
        $file
        .find('.h5p-remove')
        .click(function () {
            if (that.$add.is(':visible')) {
                confirmRemovalDialog.show($file.offset().top);
            }
            
            return false;
        });
        
        // on input update
        $file
        .find('input')
        .change(function () {
            file.metadata = { qualityName: $(this).val() };
        });
        
        // Create remove file dialog
        var confirmRemovalDialog = new H5P.ConfirmationDialog({
            headerText: H5PEditor.t('core', 'removeFile'),
            dialogText: H5PEditor.t('core', 'confirmRemoval', {':type': 'file'})
        }).appendTo(document.body);
        
        // Remove file on confirmation
        confirmRemovalDialog.on('confirmed', function () {
            that.removeFileWithElement($file);
            if (that.$files.children().length === 0) {
                that.$add.parent().find('.h5p-copyright-button').addClass('hidden');
            }
        });
    };
    
})(H5P.jQuery);
