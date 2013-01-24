YUI.add("moodle-local_mr-accessiblepanel", function(Y) {

    var BOUNDING_BOX = "boundingBox",
        HOST = "host",
        EMPTY_STR = "",
        VISIBLE = "visible",
        ARIAHIDDEN = "aria-hidden",
        ARIAROLE = "aria-role",
        ARIALABELLEDBY = "aria-labelledby",
        ARIADESCRIBEDBY = "aria-describedby",
        PANELHEADERCLASS = '.yui3-widget-hd',
        PANELBODYCLASS = '.yui3-widget-bd',
        VALIDARIAROLES = ['dialog', 'dialog-alert'];

    var AccessiblePanel = function(config) {
        AccessiblePanel.superclass.constructor.apply(this, arguments);
    }

    AccessiblePanel.NAME = "AccessiblePanel";

    AccessiblePanel.NS = "local_mr_accessiblepanel";

    AccessiblePanel.ATTRS = {

        ariarole: {
            value: "dialog"
            , writeOnce: true
            , validator: "_validateAriaRole"
        },

        ariadescribedby: {
            value: EMPTY_STR
        },

        arialabelledby: {
            value: EMPTY_STR
        }

    };

    Y.extend(AccessiblePanel, Y.Plugin.Base, {

        initializer: function() {
            this._widget = this.get(HOST);

            if (!(this._widget instanceof Y.Widget)) {
                throw new Error('Accessible Panel Plugin may only be used on Y.Widget instances');
            }

            this._widget.set('tabIndex', -1);
            this._boundingbox = this._widget.get(BOUNDING_BOX);

            // Set the aria-role attribute.
            this._boundingbox.set(ARIAROLE, this.get('ariarole'));

            // Set the aria-hidden attribute.
            this._boundingbox.set(ARIAHIDDEN, !this._widget.get(VISIBLE));

            this.afterHostMethod("show", this._afterHostShowMethod);
            this.afterHostMethod("hide", this._afterHostHideMethod);
            this.afterHostEvent("render", this._afterHostRenderEvent);

        },

        _afterHostRenderEvent : function(e) {
            var ariadescribedby = this.get('ariadescribedby'),
                arialabelledby = this.get('arialabelledby'),
                boundingbox = this._boundingbox,
                ariadescribedbynode,
                ariadescribedbyid,
                arialabelledbynode,
                arialabelledbyid;

            // Make sure we have a node that can be set as the described by
            ariadescribedbynode = Y.one(ariadescribedby);
            if (!ariadescribedbynode) {
                ariadescribedbynode = boundingbox.one(PANELBODYCLASS);
            }

            if (ariadescribedbynode) {
                // Get/create the id of the aria-describedby node.
                ariadescribedbyid = ariadescribedbynode.generateID();

                // Set the aria-describedby attribute of the bounding box.
                this._updateAriaAttr(ARIADESCRIBEDBY, ariadescribedbyid);
            }

            // Make sure we have a node that can be set as the described by
            arialabelledbynode = boundingbox.one(arialabelledby);
            if (!arialabelledbynode) {
                arialabelledbynode = boundingbox.one(PANELHEADERCLASS);
            }

            if (arialabelledbynode) {
                // Get/create the id of the aria-describedby node.
                arialabelledbyid = arialabelledbynode.generateID();

                // Set the aria-describedby attribute of the bounding box.
                this._updateAriaAttr(ARIALABELLEDBY, arialabelledbyid);
            }

            // Append some focusable divs
            this._lastfocusable = Y.Node.create('<div tabindex="0"></div>');
            this._lastfocusable.on('focus', function(e) {
                this._boundingbox.focus();
            }, this);

            this._boundingbox.append(this._lastfocusable);
        },

        _afterHostShowMethod : function() {
            // Update aria-hidden field after the panel's show method.
            this._updateAriaAttr(ARIAHIDDEN, false);

            // Give the boundingbox focus.
            this._widget.focus();
        },

        _afterHostHideMethod: function() {
            // Update aria-hidden field after the panel's hide method.
            this._updateAriaAttr(ARIAHIDDEN, true);
        },

        _updateAriaAttr: function(attr, newval) {
            this._boundingbox.set(attr, newval);
        },

        // Validate the aria-role values we want to allow.
        _validateAriaRole: function(value) {
            return (VALIDARIAROLES.indexOf(value) !== -1);
        }
    });

    M.local_mr = M.local_mr || {};
    M.local_mr.accessiblepanel = AccessiblePanel;

}, '@VERSION@', {requires:["plugin"]});
