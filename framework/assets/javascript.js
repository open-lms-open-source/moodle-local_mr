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

    if (Y.Lang.isObject(args.uldivid)) {
        var uldiv = args.uldivid;
    } else {
        var uldiv = Y.one('#' + args.uldivid);
    }

    if (!actextfield || !selectfield || !uldiv) {
        return;
    }

    var selectid = selectfield.get('id');

    // hide the selectfield mform fitem
    var selfitem = selectfield.ancestor('div.fitem');
    selfitem.setStyle('display', 'none');

    // stuffs for adding a selected item to our div
    var deleteimg = '<a href="#"><img src="' + M.util.image_url('t/delete', 'moodle') + '" alt="Remove" /></a>';
    var addselected = function(idx, text) {
        uldiv.append('<div id="' + selectid + 'smpidx_smpidx' + idx + '" class="selectmultiplusitem"><div class="optiontext">' + text + '</div><div class="deletebtn">' + deleteimg  +'</div></div>')
    };

    // prepopulate div with already selected options
    var seloptions = selectfield.all('option');
    if (seloptions instanceof Y.NodeList && !seloptions.isEmpty()) {
        var selectedoptions = seloptions.filter(function(opt) {
            return opt.selected;
        });

        selectedoptions.each(function(selopt) {
            addselected(selopt.get('index'), selopt.get('innerHTML'));
        });

        // add the addedlist class to the uldiv
        if (!selectedoptions.isEmpty() && !uldiv.hasClass('addedlist')) {
            uldiv.addClass('addedlist');
        }
    }

    // onclick event for remove links
    uldiv.delegate('click', function(e) {
        // prevent default event action
        e.preventDefault();

        // get the div for the select item entry
        var selectitemdiv = e.target.ancestor('div.selectmultiplusitem');
        var sidid = selectitemdiv.get('id');

        // get the option value
        var optionidx = sidid.split('smpidx_smpidx').pop();

        // deselect that option in our selectbox
        if (seloptions instanceof Y.NodeList && !seloptions.isEmpty()) {
            var selectedoption = seloptions.item(optionidx);
            if (selectedoption) {
                selectedoption.set('selected', '');
            }
        }

        // remove from list
        selectitemdiv.remove();

        // check to see if there are any items left/ if not remove added list class
        if (!uldiv.one('.selectmultiplusitem')) {
            uldiv.removeClass('addedlist');
        }

    }, 'div.deletebtn a');

    // plug the autocomplete widget
    actextfield.plug(Y.Plugin.AutoComplete, {
        resultTextLocator: 'text',
        activateFirstItem: true,
        minimumQueryLength: 0,
        queryDelay: 0,
        render: 'body',
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
        ],
        width: 'auto'
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
        addselected(optionidx, result.raw.text);

        // remove the text from the textbox
        actextfield.set('value', '');
        actextfield.ac.set('value', '');
        actextfield.focus();

        if (!uldiv.hasClass('addedlist')) {
            uldiv.addClass('addedlist');
        }
    });

    var pagecontent = Y.one('#report-content');
    if (pagecontent == null) {
        pagecontent = Y.one('.mr_report');
    }
    // May need to resize the report content div based on size of the auto complete list
    actextfield.ac.on('visibleChange', function(e) {
        if (pagecontent && !e.newVal && e.prevVal) {
            // visible to hidden
            pagecontent.setStyle('min-height', 600);
        } else if (pagecontent && e.newVal && !e.prevVal) {
            // hidden to visible
            var pagecontentypos = pagecontent.getY();
            var pagecontentheight = pagecontent.get('offsetHeight');
            var acheight = actextfield.ac.get('boundingBox').get('offsetHeight');
            var acy = actextfield.ac.get('y');

            var acybottom = acy + acheight;
            var pagecontentbottom = pagecontentypos + pagecontentheight;

            if (acybottom > pagecontentbottom) {
                var heightdiff = acybottom - pagecontentbottom;
                pagecontent.setStyle('min-height', pagecontentheight + heightdiff);
            }
        }
    });
};

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
        var myDataSource             = new Y.YUI2.util.XHRDataSource(args.url);
        myDataSource.responseType    = Y.YUI2.util.XHRDataSource.TYPE_JSON;
        myDataSource.maxCacheEntries = 5;
        myDataSource.responseSchema  = {
            resultsList: "results",
            fields : args.fields
        };
    } else {
        // Use a local datasource
        var myDataSource = new Y.YUI2.util.LocalDataSource(args.data);
        myDataSource.responseSchema = {fields : args.fields};
    }

    // Instantiate the AutoComplete
    var myAutoComplete = new Y.YUI2.widget.AutoComplete(myInputField, myContainerId, myDataSource);
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
        var myHiddenField = Y.YUI2.util.Dom.get('id_' + args.hiddenfieldname);
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
};
