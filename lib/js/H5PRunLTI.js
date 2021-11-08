(function ($) {
    runLti();

    function runLti() {
        var ltiParams = opencastLTIParamsAjaxCallSync();
        // Does not perform the LTI, in case the ltiParams is not configured in the admin or there is some error!
        if (ltiParams == null || !ltiParams) { 
            return;
        }
        var endpoint = ltiParams.endpoint;
        delete ltiParams.endpoint;
        var ltiLaunchForm = H5P.jQuery('<form action="' + endpoint + '" id="ltiLaunchForm" name="ltiLaunchForm" encType="application/x-www-form-urlencoded" method="POST"></form>');
        for (var [paramName, paramValue] of Object.entries(ltiParams)) {
            var hiddenInput = H5P.jQuery('<input type="hidden" name="' + paramName + '" value="' + paramValue + '">');
            hiddenInput.appendTo(ltiLaunchForm);
        }
        var container = document.querySelector('.h5p-iframe');
        if (!container && H5P.$body) {
            container = H5P.$body.get()[0];
        }
        ltiLaunchForm.appendTo(container);
        
        $('#ltiLaunchForm').submit(function(e) {
            e.preventDefault();
            var ocurl = decodeURIComponent($(this).attr("action"));
            $.ajax({
                url: ocurl,
                crossDomain: true,
                type: 'POST',
                xhrFields: {withCredentials: true},
                data: $('#ltiLaunchForm').serialize(),
                completed: () => {
                    console.log('H5P Opencast LTI (COMPLETED): LTI auth attempt is completed!');
                },
                success: () => {
                    console.log('H5P Opencast LTI (SUCCESS): successfully performed.');
                },
                error: function (request, status, error) {
                    var errorMessage = request.responseText ? request.responseText : 'Most likely "Cross-Origin Request Blocked" happend, due to the fact that the opencast server is unable to accept the LTI call from moodle server!';
                    console.log('H5P Opencast LTI (FAILED): ' + errorMessage);
                },
                async: false
            });
        });
        ltiLaunchForm.submit();
    }

    function getOpencastLTIActionUrl() {

        var urlParams = new URLSearchParams(window.location.search);
        var currentUrl = window.location.href;

        var courseId = urlParams.has('course') ? urlParams.get('course') : '';
        var id = urlParams.has('id') ? urlParams.get('id') : '';
        var update = urlParams.has('update') ? urlParams.get('update') : '';
        var url = urlParams.has('url') ? urlParams.get('url') : '';
        var contentId = (window.H5PEditor !== undefined) ? window.H5PEditor.contentId : '';
        var contextId = urlParams.has('contextid') ? urlParams.get('contextid') : '';

        var splitStr = '';
        var pluginName = '';

        if (url) { // Compatible with h5p core.
            pluginName = 'och5pcore';
            var urlSplited = url.split('.php/')[1];
            var contextId = urlSplited.split('/')[0];
            splitStr = '/h5p/embed.php';
        } else { // Compatible with mod_hvp.
            pluginName = 'och5p';
            splitStr = '/mod/hvp'; 
            if (currentUrl.includes('/course/modedit.php')) {
                splitStr = '/course/modedit.php';
            }
        }

        var baseUrl = currentUrl.split(splitStr)[0];

        var ocAjaxPath = baseUrl + '/local/' + pluginName + '/ajax.php?action=ltiParams';
        if (id) {
            ocAjaxPath += '&id=' + id;
        } else if (update) {
            ocAjaxPath += '&id=' + update;
        }

        if (courseId) {
            ocAjaxPath += '&courseid=' + courseId;
        }

        if (contentId) {
            ocAjaxPath += '&contentid=' + contentId;
        }

        if (contextId) {
            ocAjaxPath += '&contextid=' + contextId;
        }
        return ocAjaxPath;
    }

    function opencastLTIParamsAjaxCallSync() {
        let result = null;
        $.ajax({
            url: getOpencastLTIActionUrl(),
            success: (data) => {
                if (data.error) {
                    console.log(data.error);
                    return;
                }
                result = data.result;
            },
            error: (request, status, error) => {
                console.log(request.responseText);
            },
            async: false
        });

        return result;
    }
    
})(H5P.jQuery);