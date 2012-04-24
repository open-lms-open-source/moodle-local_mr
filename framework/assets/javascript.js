/**
 * @namespace M.local_mr
 */
M.local_mr = M.local_mr || {};

/**
 *
 * @param Y
 * @param args
 */
M.local_mr.init_filter_selectmultiplus = function(Y, args) {
    if (Y.Lang.isObject(args.selectname)) {
        var selectfield = args.selectname;
    } else {
        var selectfield = Y.one('#id_' + args.selectname);
    }

    if (Y.Lang.isObject(args.textname)) {
        var actextfield = args.textname;
    } else {
        var actextfield = Y.one('#id_' + args.textname);
    }

    if (!actextfield || !selectfield) {
        return;
    }

    // add a container for putting selected items in
//    var listdiv = Y.Node.create('<div><ul></ul></div>')
//        .appendTo(selectfield.get('parentNode'));
//
//    // move the container to where the select box should be
//    var xy = selectfield.getXY();
//    listdiv.setXY(xy[0], xy[1]);
//
//    // get the list
//    var list = listdiv.one('ul');


    // plug the autocomplete widget
    actextfield.plug(Y.Plugin.AutoComplete, {
        resultTextLocator: 'text',
        activateFirstItem: true,
        minimumQueryLength: 0,
        queryDelay: 0,
        source: selectfield,
        resultFilters: [
            function(query, results) {
                return Y.Array.filter(results, function(result) {
                    // only include results that are NOT already selected in the multiselect
                    return !result.raw.selected;
                });
            },
            'charMatch',
            'subWordMatch'
        ]
    });

    // Send an empty request on spacebar key to show entire list
    actextfield.on('key', function(e) {
        if (!actextfield.get('value').match(/^\s*?$/)) {
            return;
        }

        e.preventDefault();

        //Send an empty query to trigger all results
        actextfield.ac.sendRequest('');

    }, '32');

    // wire the autocomplete select event
    actextfield.ac.on('select', function(e) {
        // get the result
        var result = e.result;

        // prevent the default behavoir
        e.preventDefault();

        // hide the autocomplete list
        actextfield.ac.hide();

        // select the item in the selectbox
        var optionidx = result.raw.index;
        var options = selectfield.get('options');
        options.item(optionidx).set('selected', 'selected');

        // check to see if it's already in the list
//        var existingli = list.one('#id_' + args.selectname + 'li_' + result.raw.value);
//        if (!existingli) {
//            list.append('<li id="id_' + args.selectname + 'li_' + result.raw.value + '">' + result.raw.text + '</li>');
//        }

        // remove the text from the textbox
        actextfield.set('value', '');
        actextfield.focus();
    });
}

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