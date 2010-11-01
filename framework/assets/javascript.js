/**
 * @namespace M.local_mr
 */
M.local_mr = M.local_mr || {};

/**
 * Generate YUI autocomplete for Moodle form elements
 *
 * @namespace M.local_mr
 * @function
 * @param {YUI} Y
 * @param {object} args
 */
M.local_mr.init_mr_html_autocomplete = function(Y, args) {
    var myContainerId = 'id_autocontainer_' + args.fieldname;
    var myInputField  = document.getElementById('id_' + args.fieldname);

    // Surround input field in a div to control width
    var myWrapperDiv = document.createElement('div');
    myWrapperDiv.setAttribute('style', 'width: ' + args.width + 'px;');
    myInputField.parentNode.appendChild(myWrapperDiv);
    myWrapperDiv.appendChild(myInputField);

    // Add container div for the autocomplete list
    var myContainerDiv = document.createElement('div');
    myContainerDiv.setAttribute('id', myContainerId);
    myContainerDiv.setAttribute('style', 'width: ' + args.width + 'px;');
    myWrapperDiv.appendChild(myContainerDiv);

    if (args.url != null) {
        // Use a remote datasource
        var myDataSource             = new YAHOO.util.XHRDataSource(args.url);
        myDataSource.responseType    = YAHOO.util.XHRDataSource.TYPE_JSON;
        myDataSource.maxCacheEntries = 5;
        myDataSource.responseSchema  = {
            resultsList: "results",
            fields : args.fields
        };
    } else {
        // Use a local datasource
        var myDataSource = new YAHOO.util.LocalDataSource(args.data);
        myDataSource.responseSchema = {fields : args.fields};
    }

    // Instantiate the AutoComplete
    var myAutoComplete = new YAHOO.widget.AutoComplete(myInputField, myContainerId, myDataSource);
    myAutoComplete.useShadow           = true;
    myAutoComplete.maxResultsDisplayed = 20;
    myAutoComplete.applyLocalFilter    = true;
    myAutoComplete.queryMatchContains  = true;

    if (args.url != null) {
        // Increase delay to reduce server hits
        myAutoComplete.queryDelay = .5;

        // YUI isn't great at appending the query, let's do it for ourselves
        myAutoComplete.generateRequest = function(sQuery) {
            if (args.url.search('/\?/') != -1) {
                return '&query=' + sQuery;
            } else {
                return '?query=' + sQuery;
            }
        };
    }

    if (args.hiddenfieldname != '') {
        var myHiddenField = YAHOO.util.Dom.get('id_' + args.hiddenfieldname);
        var mySelectHandler = function(sType, aArgs) {
            // Selected item's result data
            var oData = aArgs[2];

            // Update hidden form field with the selected item's ID
            myHiddenField.value = oData[1];
        };
        var myEnforceHandler = function() {
            myHiddenField.value = 0;
        };
        var unmatchedItemSelectHandler = function(oSelf , sSelection) {
            // Instead of using myAutoComplete.forceSelection
            // need to use this otherwise defaults get cleared onfocus/unfocus
            if (myAutoComplete._sInitInputValue != myInputField.value) {
                myAutoComplete._clearSelection();
            }
        };
        myAutoComplete.itemSelectEvent.subscribe(mySelectHandler);
        myAutoComplete.selectionEnforceEvent.subscribe(myEnforceHandler);
        myAutoComplete.unmatchedItemSelectEvent.subscribe(unmatchedItemSelectHandler);
    }
}