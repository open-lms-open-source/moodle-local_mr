YUI.add("moodle-local_mr-accessiblepanel", function(Y) {

/* Any frequently used shortcuts, strings and constants */
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

/* MyPlugin class constructor */
function AccessiblePanel(config) {
    AccessiblePanel.superclass.constructor.apply(this, arguments);
}

/*
 * Required NAME static field, to identify the class and
 * used as an event prefix, to generate class names etc. (set to the
 * class name in camel case).
 */
AccessiblePanel.NAME = "AccessiblePanel";

/*
 * Required NS static field, to identify the property on the host which will,
 * be used to refer to the plugin instance ( e.g. host.feature.doSomething() )
 */
AccessiblePanel.NS = "ap";

/*
 * The attribute configuration for the plugin. This defines the core user facing state of the plugin
 */
AccessiblePanel.ATTRS = {

    ariarole: {
        value: "dialog"                    // The default value for attrA, used if the user does not set a value during construction.
        , writeOnce: true            // Can only be set once by the end user (usually during construction). Can be set by the component developer at any time, using _set
        , validator: "_validateAriaRole"  // Used to validate attrA's value before updating it. Refers to a prototype method, to make customization easier
    },

    ariadescribedby: {
        value: EMPTY_STR
    },

    arialabelledby: {
        value: EMPTY_STR
    }

    // ... attrB, attrC, attrD ... attribute configurations.

    // Can also include attributes for the super class if you want to override or add configuration parameters
};

/* MyPlugin extends the base Plugin.Base class */
Y.extend(AccessiblePanel, Y.Plugin.Base, {

    initializer: function() {
        /*
         * initializer is part of the lifecycle introduced by
         * the Base class. It is invoked during construction, when
         * the plugin is plugged into the host, and can be used to
         * register listeners, or inject logic before or after methods
         * on the host.
         *
         * It does not need to invoke the superclass initializer.
         * init() will call initializer() for all classes in the hierarchy.
         */
        this._widget = this.get(HOST),
        this._boundingbox = this._widget.get(BOUNDING_BOX);

        // Set the aria-role attribute.
        this._boundingbox.set(ARIAROLE, this.get('ariarole'));

        // Set the aria-hidden attribute.
        this._boundingbox.set(ARIAHIDDEN, !this._widget.get(VISIBLE));

        // See Y.Do.before, Y.Do.after
        this.afterHostMethod("show", this._afterHostShowMethod);
        this.afterHostMethod("hide", this._afterHostHideMethod);

        // See Y.EventTarget.on, Y.EventTarget.after
        this.afterHostEvent("render", this._afterHostRenderEvent);

    },

    destructor : function() {
        /*
         * destructor is part of the lifecycle introduced by
         * the Base class. It is invoked when the plugin is unplugged.
         *
         * Any listeners registered using Plugin.Base's onHostEvent/afterHostEvent methods,
         * or any methods displaced using it's beforeHostMethod/afterHostMethod methods
         * will be detached/restored by Plugin.Base's destructor.
         *
         * We only need to clean up anything we change on the host
         *
         * It does not need to invoke the superclass destructor.
         * destroy() will call initializer() for all classes in the hierarchy.
         */
    },

    /* Supporting Methods */

    _onHostRenderEvent : function(e) {
        /* React on the host render event */
    },


    _afterHostRenderEvent : function(e) {
        /* React after the host render event */
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
        this._firstfocusable = Y.Node.create('<div tabindex="0"></div>');
        this._lastfocusable = Y.Node.create('<div tabindex="0"></div>');
//
        this._lastfocusable.on('focus', function(e) {
            this._firstfocusable.focus();
        }, this);

        this._boundingbox.prepend(this._firstfocusable);
        this._boundingbox.append(this._lastfocusable);
    },

    _afterHostShowMethod : function() {
        /* Inject logic after the host's show method is called. */

        // Update aria-hidden field after the panel's show method.
        this._updateAriaAttr(ARIAHIDDEN, false);

        // Give the boundingbox focus.
        this._firstfocusable.focus();
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

    Y.namespace("Plugin.Moodle.local_mr").AccessiblePanel = AccessiblePanel;

}, '@VERSION@', {requires:["plugin"]});
