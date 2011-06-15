/**
 * @namespace M.local_mr
 */
M.local_mr = M.local_mr || {};

/**
 * Render mr_html_table and mr_html_paging with YUI
 *
 * @namespace M.local_mr
 * @function
 * @param {YUI} Y
 * @param {object} args
 */
M.local_mr.init_mr_html_table = function(Y, args) {
    if (!args.autoload) {
        var thisInstance = this;
        var theseArgs = arguments;

        args.autoload = true;

        // Create a function reference so this can be called later to load the table
        var loadFunction = window[args.id + "_load"] = function() {
            thisInstance.init_mr_html_table.apply(thisInstance, theseArgs);
        }
        return;
    }

    // Table's DataSource
    var myDataSource             = new YAHOO.util.DataSource(args.url);
    myDataSource.responseType    = YAHOO.util.DataSource.TYPE_JSON;
    myDataSource.maxCacheEntries = 0;
    myDataSource.responseSchema  = {
        resultsList: "records",
        fields: args.columns,
        metaFields: {
            totalRecords: "totalRecords", // Access to value in the server response
            emptyMessage: "emptyMessage"
        }
    };

    // Table pagination configuration
    var myPaginatorConfig = {
        rowsPerPage:   args.perpage,
        alwaysVisible: false,
        totalRecords: Number.MAX_VALUE, // Setting totalRecords arbitrarily high so that the initialPage setting will work. This doesn't affect behavior as it is overwritten when actual data is loaded.
        initialPage: args.page + 1
    };

    // Add per page options to paginator
    if (args.perpageopts.length > 0) {
        myPaginatorConfig.template = YAHOO.widget.Paginator.TEMPLATE_ROWS_PER_PAGE;
        myPaginatorConfig.rowsPerPageOptions = args.perpageopts;
    }

    // Build custom requests
    var myRequestBuilder = function(oState, oSelf) {
        // Get states or use defaults
        oState      = oState || { pagination: null, sortedBy: null };
        var sort    = (oState.sortedBy) ? oState.sortedBy.key : args.sort;
        var page    = (oState.pagination) ? oState.pagination.recordOffset : (args.page * args.perpage);
        var perpage = (oState.pagination) ? oState.pagination.rowsPerPage : args.perpage;

        if (oState.sortedBy) {
            var dir = oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC ? args.desc : args.asc;
        } else {
            var dir = args.order;
        }
        if (page != 0) {
            page = (page / perpage);
        }
        return  "&tsort=" + sort +
                "&torder=" + dir +
                "&tpage=" + page +
                "&tperpage=" + perpage;
    };

    // DataTable configuration
    var myDataTableConfigs = {
        initialRequest:  myRequestBuilder(),
        MSG_LOADING:     args.loadingmsg,
        dynamicData:     true,
        paginator:       new YAHOO.widget.Paginator(myPaginatorConfig),
        generateRequest: myRequestBuilder,
        sortedBy: {
            key: args.sort,
            dir: (args.order == args.asc) ? YAHOO.widget.DataTable.CLASS_ASC : YAHOO.widget.DataTable.CLASS_DESC
        }
    };

    // DataTable instance
    var myDataTable = new YAHOO.widget.DataTable(args.id, args.columns, myDataSource, myDataTableConfigs);

    // Update totalRecords and empty message on the fly with value from server
    myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
        oPayload.totalRecords = oResponse.meta.totalRecords;
        myDataTable.set('MSG_EMPTY', oResponse.meta.emptyMessage);

        return oPayload;
    }

    //Store a reference to the table so it can be accessed easily later
    window[args.id] = myDataTable;
};