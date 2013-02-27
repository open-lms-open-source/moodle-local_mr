YUI.add('moodle-local_mr-ariacontrol', function(Y) {
    var ARIACONTROLNAME = 'local_mr_ariacontrol',

        // Events
        EVT_BEFORE_TOGGLE = 'beforeToggle',
        EVT_AFTER_TOGGLE = 'afterToggle',
        EVT_BEFORE_LABEL_TOGGLE = 'beforeLabelToggle',
        EVT_AFTER_LABEL_TOGGLE = 'afterLabelToggle',

        // Shortcuts, etc
        HOST = 'host',
        ROLE_ATTR = 'role',
        ARIA_LABEL_ATTR = 'aria-label',
        ARIA_CONTROLS_ATTR = 'aria-controls',
        BOUNDING_BOX = 'boundingBox',
        Lang = Y.Lang;

    var ARIACONTROL = function() {
        ARIACONTROL.superclass.constructor.apply(this, arguments);
    };

    Y.extend(ARIACONTROL, Y.Plugin.Base,
        {
            initializer: function() {
                // Optionally update role
                if (this.get('role') !== '') {
                    this.get('box').setAttribute(ROLE_ATTR, this.get('role'));
                }
                // Optionally wire up event
                if (this.get('event') !== '') {
                    this.onHostEvent(this.get('event'), this.handle_host_event);
                }
                // Optionally update aria-label
                this.toggle_aria_label();

                // Optionally update aria-controls
                this._init_aria_controls();

                // If the ariaControls ATTR is modified, update our host
                this.after('ariaControlsChange', this._init_aria_controls);
            },

            /**
             * Optionally update aria-controls attribute
             * @private
             */
            _init_aria_controls: function() {
                if (!Lang.isNull(this.get('ariaControls'))) {
                    this.get('box').setAttribute(ARIA_CONTROLS_ATTR, this.get('ariaControls').generateID());
                }
            },

            /**
             * Determine if we are updating the label or not
             * @return {Boolean}
             */
            is_label_updating: function() {
                return (this.get('beforeAriaLabel') !== '' && this.get('afterAriaLabel') !== '')
            },

            /**
             * Event handler for when an action is taken on the host
             *
             * This will update the aria-label and prevent default on the
             * event.
             * @param e
             */
            handle_host_event: function(e) {
                e.preventDefault();
                this.toggle_state();
            },

            /**
             * Toggle the state of the host by swapping labels
             * and notifying the controlled element to also
             * update its state.
             */
            toggle_state: function() {
                this.fire(EVT_BEFORE_TOGGLE);

                this.toggle_aria_label();

                if (!Lang.isNull(this.get('ariaControls')) && !Lang.isUndefined(this.get('ariaControls').local_mr_ariacontrolled)) {
                    this.get('ariaControls').local_mr_ariacontrolled.toggle_state();
                }
                this.fire(EVT_AFTER_TOGGLE);
            },

            /**
             * Updates the aria-label attribute on the host.  This is
             * handy when the label needs to change to reflect the current
             * state.  EG: swap "Hide topic Foo" with "Show topic Foo"
             *
             * Optionally, it can also update the title attribute
             * if the host is a link, the alt attribute if the host
             * is an image or the innerHTML if the host is a button.
             */
            toggle_aria_label: function() {
                if (!this.is_label_updating()) {
                    return;
                }
                this.fire(EVT_BEFORE_LABEL_TOGGLE);

                var box = this.get('box');
                var newLabel = '';
                if (!box.hasAttribute(ARIA_LABEL_ATTR) || box.getAttribute(ARIA_LABEL_ATTR) == this.get('afterAriaLabel')) {
                    newLabel = this.get('beforeAriaLabel');
                } else {
                    newLabel = this.get('afterAriaLabel');
                }
                box.setAttribute(ARIA_LABEL_ATTR, newLabel);

                if (!this.get('updateAriaLabelOnly')) {
                    if (box.test('a')) {
                        box.setAttribute('title', newLabel);
                    } else if (box.test('img')) {
                        box.setAttribute('alt', newLabel);
                    } else if (box.test('button')) {
                        box.set('text', newLabel);
                    }
                }

                this.fire(EVT_AFTER_LABEL_TOGGLE);
            },

            /**
             * Extract data from the host (EG: "data-" attributes)
             * @param name
             * @param defaultValue
             * @return {*}
             */
            _get_data: function(name, defaultValue) {
                var data = this.get(HOST).getData(name);
                if (!Lang.isUndefined(data)) {
                    return data;
                }
                return defaultValue;
            },

            /**
             * aria-controls setter - if the value
             * is null, preserve the null.  Otherwise,
             * transform it to a Node instance.
             * @param value
             * @return {*}
             */
            _setAriaControls: function(value) {
                if (Lang.isNull(value)) {
                    return null;
                }
                return Y.one(value);
            },

            /**
             * Validate aria-controls, either must be null
             * or can be resolved to a Node instance
             * @param value
             * @return {Boolean}
             */
            _validateAriaControls: function(value) {
                if (Lang.isNull(value)) {
                    return true;
                }
                return (Y.one(value) instanceof Y.Node);
            }
        },
        {
            NAME: ARIACONTROLNAME,
            NS: ARIACONTROLNAME,
            ATTRS: {
                /**
                 * The role attribute to set on the host.
                 *
                 * If set to an empty string, then the role attribute will
                 * not be set.
                 *
                 * Defaults to the hosts role attribute value if it exists,
                 * otherwise defaults to button.
                 *
                 * Possible roles: http://oaa-accessibility.org/examples/roles/
                 */
                role: {
                    valueFn: function() {
                        if (this.get(HOST).hasAttribute('role')) {
                            return this.get(HOST).getAttribute('role');
                        }
                        return 'button';
                    },
                    validator: Lang.isString,
                    writeOnce: true
                },
                /**
                 * The node or CSS selector string of the element that
                 * is controlled by the host.
                 *
                 * The host will get an aria-controls="ID" attribute.
                 *
                 * Examples: http://oaa-accessibility.org/examples/prop/162/
                 */
                ariaControls: {
                    value: null,
                    validator: '_validateAriaControls',
                    setter: '_setAriaControls'
                },
                /**
                 * The event that triggers the change of the aria-label
                 * attribute on the host.
                 *
                 * If set to an empty string, then no event will be wired.
                 *
                 * Requires both beforeAriaLabel and afterAriaLabel to be set
                 * to non-empty strings.
                 */
                event: {
                    value: 'click',
                    validator: Lang.isString,
                    writeOnce: true
                },
                /**
                 * Update the aria-label attribute on the host to this
                 * value.  This is the initial label value.
                 *
                 * The default value is whatever is in the host's "data-before-aria-label"
                 * attribute, otherwise, empty string.  Example: the host is:
                 * <div data-before-aria-label="Show topic foo"> then the default would be
                 * "Show topic foo"
                 *
                 * Example value: "Show topic Foo"
                 *
                 * If set to an empty string, then no label updating will happen.
                 */
                beforeAriaLabel: {
                    valueFn: function() {
                        return this._get_data('before-aria-label', '');
                    },
                    validator: Lang.isString,
                    setter: Y.Escape.html
                },

                /**
                 * Update the aria-label attribute on the host to this
                 * value after an event.
                 *
                 * The default value is whatever is in the host's "data-after-aria-label"
                 * attribute, otherwise, empty string.  Example: the host is:
                 * <div data-after-aria-label="Hide topic foo"> then the default would be
                 * "Hide topic foo"
                 *
                 * Example value: "Hide topic Foo"
                 *
                 * If set to an empty string, then no label updating will happen.
                 */
                afterAriaLabel: {
                    valueFn: function() {
                        return this._get_data('after-aria-label', '');
                    },
                    validator: Lang.isString,
                    setter: Y.Escape.html
                },
                /**
                 * When updating aria-label, this plugin can also update the
                 * title attribute if the host is a link, the alt attribute
                 * if the host is an image or the innerHTML if the host is a button.
                 *
                 * Set this to true to disable this extra functionality
                 */
                updateAriaLabelOnly: {
                    value: false,
                    validator: Lang.isBoolean
                },
                /**
                 * Private and for internal use
                 *
                 * Holds the value of the element that receives the
                 * event listener, attributes, etc.
                 */
                box: {
                    readOnly: true,
                    valueFn: function() {
                        if (this.get(HOST) instanceof Y.Widget) {
                            return this.get(HOST).get(BOUNDING_BOX);
                        }
                        return this.get(HOST);
                    }
                }
            }
        }
    );

    M.local_mr = M.local_mr || {};
    M.local_mr.ariacontrol = ARIACONTROL;

}, '@VERSION@', {
    requires: ['plugin', 'widget', 'escape']
});
