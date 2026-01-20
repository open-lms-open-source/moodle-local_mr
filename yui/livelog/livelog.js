/**
 * Lots of good information about what this does:
 *
 * https://developer.mozilla.org/en-US/docs/Accessibility/ARIA/ARIA_Live_Regions
 * http://oaa-accessibility.org/example/23/
 *
 * Basically this widget creates a hidden (by default) log that is
 * read to screen readers.  Behavior of the reading can be changed
 * by changing the attributes.
 */
YUI.add('moodle-local_mr-livelog', function(Y) {
    var BOX = 'contentBox',
        LOG_BOX_TEMPLATE = '<div></div>';

    var LIVE_LOG = function() {
        LIVE_LOG.superclass.constructor.apply(this, arguments);
    };

    LIVE_LOG.NAME = 'local_mr_livelog';

    LIVE_LOG.ATTRS = {
        // This is the box that contains all of the log entries.
        logBox: {value: Y.Node.create(LOG_BOX_TEMPLATE), readOnly: true},
        classNames: {value: 'accesshide', validator: Y.Lang.isString},
        logTemplate: {value: '<p></p>', validator: Y.Lang.isString},
        // When log entries are read. Possible values: off, polite and assertive
        ariaLive: {value: 'polite', validator: Y.Lang.isString},
        // Which log entries are read. Possible values: additions, removals, text and all.  Can combine, EG: "additions removals"
        ariaRelevant: {value: 'additions text', validator: Y.Lang.isString},
        // Read the live region as a whole or not. Possible values: true or false
        ariaAtomic: {value: 'false', validator: Y.Lang.isString}
    };

    Y.extend(LIVE_LOG, Y.Widget,
        {
            renderUI: function() {
                this.get(BOX).append(this.get('logBox'));
                this.update_log_attrs();
            },

            bindUI: function() {
                this.after(['ariaLiveChange', 'ariaRelevant', 'ariaAtomic', 'classNames'], this.update_log_attrs, this);
            },

            // Add some text to the log.
            log_text: function(logText) {
                var logNode = Y.Node.create(this.get('logTemplate'));
                logNode.set('text', logText);

                this.log_node(logNode);
            },

            // Add a YUI Node to the log.  Allows for complete customization of log nodes.
            log_node: function(logNode) {
                this.get('logBox').append(logNode);

                // Fire after so node is part of DOM tree.
                this.fire('logAdded', {}, logNode);
            },

            update_log_attrs: function() {
                this.get('logBox').setAttribute('role', 'log')
                    .setAttribute('class', this.get('classNames'))
                    .setAttribute('aria-relevant', this.get('ariaRelevant'))
                    .setAttribute('aria-atomic', this.get('ariaAtomic'))
                    .setAttribute('aria-live', this.get('ariaLive'));
            }
        }
    );

    M.local_mr = M.local_mr || {};
    M.local_mr.LiveLog = LIVE_LOG;
    M.local_mr.init_livelog = function(config) {
        var widget = new LIVE_LOG(config);
        widget.render();
        return widget;
    };
}, '@VERSION@', {
    requires: ['widget']
});
