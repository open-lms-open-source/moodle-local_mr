/**
 * @namespace M.local_mr
 */
M.local_mr = M.local_mr || {};

/**
 * @type {M.local_mr.LiveLog}
 */
M.local_mr.tableLiveLog = undefined;

/**
 * Render mr_html_table and mr_html_paging with YUI
 *
 * @namespace M.local_mr
 * @function
 * @param Y
 * @param {object} args
 */
M.local_mr.init_mr_html_table = function(Y, args) {
    // Load this regardless of auto-load.
    M.local_mr.init_table_live_log(Y);

    if (!args.autoload) {
        var thisInstance = this;
        var theseArgs = arguments;

        args.autoload = true;

        // Create a function reference so this can be called later to load the table
        var loadFunction = window[args.id + "_load"] = function() {
            thisInstance.init_mr_html_table.apply(thisInstance, theseArgs);
        };
        return;
    }

    // Table's DataSource
    var myDataSource             = new Y.YUI2.util.DataSource(args.url);
    myDataSource.responseType    = Y.YUI2.util.DataSource.TYPE_JSON;
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
        initialPage: args.page + 1,

        firstPageLinkLabel : "<< " + M.str.local_mr.paginatorfirstlabel,
        firstPageLinkTitle : M.str.local_mr.paginatorfirsttitle,

        lastPageLinkLabel : M.str.local_mr.paginatorlastlabel + " >>",
        lastPageLinkTitle : M.str.local_mr.paginatorlasttitle,

        previousPageLinkLabel : "< " + M.str.local_mr.paginatorprevlabel,
        previousPageLinkTitle : M.str.local_mr.paginatorprevtitle,

        nextPageLinkLabel : M.str.local_mr.paginatornextlabel + " >",
        nextPageLinkTitle : M.str.local_mr.paginatornexttitle
    };

    // Add per page options to paginator
    if (args.perpageopts.length > 0) {
        myPaginatorConfig.template = Y.YUI2.widget.Paginator.TEMPLATE_ROWS_PER_PAGE;
        myPaginatorConfig.rowsPerPageOptions = args.perpageopts;
        myPaginatorConfig.alwaysVisible = true;
    }

    // Build custom requests
    var myRequestBuilder = function(oState, oSelf) {
        // Get states or use defaults
        oState      = oState || { pagination: null, sortedBy: null };
        var sort    = (oState.sortedBy) ? oState.sortedBy.key : args.sort;
        var page    = (oState.pagination) ? oState.pagination.recordOffset : (args.page * args.perpage);
        var perpage = (oState.pagination) ? oState.pagination.rowsPerPage : args.perpage;

        if (oState.sortedBy) {
            var dir = oState.sortedBy.dir === Y.YUI2.widget.DataTable.CLASS_DESC ? args.desc : args.asc;
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
        paginator:       new Y.YUI2.widget.Paginator(myPaginatorConfig),
        generateRequest: myRequestBuilder,
        sortedBy: {
            key: args.sort,
            dir: (args.order == args.asc) ? Y.YUI2.widget.DataTable.CLASS_ASC : Y.YUI2.widget.DataTable.CLASS_DESC
        }
    };

    if (args.summary !== undefined) {
        myDataTableConfigs.summary = args.summary;
    }
    if (args.caption !== undefined) {
        myDataTableConfigs.caption = args.caption;
    }

    // DataTable instance
    var myDataTable = new Y.YUI2.widget.DataTable(args.id, args.columns, myDataSource, myDataTableConfigs);

    // Update totalRecords and empty message on the fly with value from server
    myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
        oPayload.totalRecords = oResponse.meta.totalRecords;
        myDataTable.set('MSG_EMPTY', oResponse.meta.emptyMessage);

        return oPayload;
    };

    myDataTable.subscribe('columnSortEvent', function(e) {
        var identifier = e.dir === Y.YUI2.widget.DataTable.CLASS_DESC ? 'tablesortedbydesc' : 'tablesortedbyasc';
        M.local_mr.tableLiveLog.log_text(M.util.get_string(identifier, 'local_mr', e.column.label));
    });

    //Store a reference to the table so it can be accessed easily later
    window[args.id] = myDataTable;
};

// Singleton!
M.local_mr.init_table_live_log = function(Y) {
    if (Y.Lang.isUndefined(M.local_mr.tableLiveLog)) {
        M.local_mr.tableLiveLog = M.local_mr.init_livelog();
    }
};