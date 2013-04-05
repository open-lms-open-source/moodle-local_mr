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

        /**
         * The role attribute to set on the host.
         *
         * If set to an empty string, then the  role attribute will
         * not be set.
         *
         * Possible roles: 'dialog', 'dialog-alert'
         */
        ariaRole: {
            value: "dialog"
            , writeOnce: true
            , validator: "_validateAriaRole"
        },

        /**
         * The Y.Node or CSS selector string of the element that
         * describes the host.
         *
         * The host will get an aria-describedby="ID" attribute.
         *
         * If nothing is passed and '.yui3-widget-bd' exists it will be used.
         *
         * Examples: http://oaa-accessibility.org/examples/prop/163/
         */
        ariaDescribedBy: {
            value: EMPTY_STR
        },

        /**
         * The Y.Node or CSS selector string of the element that
         * labels the host.
         *
         * The host will get an aria-labelledby="ID" attribute.
         *
         * If nothing is passed and '.yui3-widget-hd' exists it will be used.
         *
         * Examples: http://oaa-accessibility.org/examples/prop/165/
         */
        ariaLabelledBy: {
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
            this._boundingbox.set(ARIAROLE, this.get('ariaRole'));

            // Set the aria-hidden attribute.
            this._boundingbox.set(ARIAHIDDEN, !this._widget.get(VISIBLE));

            this.afterHostMethod("show", this._afterHostShowMethod);
            this.afterHostMethod("hide", this._afterHostHideMethod);
            this.afterHostEvent("render", this._afterHostRenderEvent);

        },

        /**
         * Called after the host's render event. Adds the aria-describedby and aria-labelledby attributes
         * to the Widget's bounding box if the nodes exists. Also sets up some focus management on the Widget.
         *
         * @param e
         * @private
         */
        _afterHostRenderEvent : function(e) {
            var ariadescribedby = this.get('ariaDescribedBy'),
                arialabelledby = this.get('ariaLabelledBy'),
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

            // Append a focusable last div.
            this._lastfocusable = Y.Node.create('<div tabindex="0"></div>');
            this._lastfocusable.on('focus', function(e) {
                this._boundingbox.focus();
            }, this);

            this._boundingbox.append(this._lastfocusable);
        },

        /**
         * Called after the host's show method. Updates the 'aria-hidden' attribute and sets the focus on the
         * widget.
         *
         * @private
         */
        _afterHostShowMethod : function() {
            // Update aria-hidden field after the panel's show method.
            this._updateAriaAttr(ARIAHIDDEN, false);

            // Give the boundingbox focus.
            this._widget.focus();
        },

        /**
         * Call after the hosts hide method. Updates the 'aria-hidden' attribute.
         * @private
         */
        _afterHostHideMethod: function() {
            // Update aria-hidden field after the panel's hide method.
            this._updateAriaAttr(ARIAHIDDEN, true);
        },

        /**
         * Updates an attribute on the bounding box
         *
         * @param attr - attribute to update
         * @param newval - new value to set
         * @private
         */
        _updateAriaAttr: function(attr, newval) {
            this._boundingbox.set(attr, newval);
        },

        /**
         * ariaRole ATTR validation.
         *
         * @param value
         * @return {Boolean}
         * @private
         */
        _validateAriaRole: function(value) {
            return (Y.Array.indexOf(VALIDARIAROLES, value) !== -1);
        }
    });

    M.local_mr = M.local_mr || {};
    M.local_mr.accessiblepanel = AccessiblePanel;

}, '@VERSION@', {requires:['plugin', 'widget']});
