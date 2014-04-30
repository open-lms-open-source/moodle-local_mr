YUI.add('moodle-local_mr-ariacontrolled', function(Y) {
    var ARIACONTROLLEDNAME = 'local_mr_ariacontrolled',

        // Events
        EVT_BEFORE_TOGGLE = 'beforeToggle',
        EVT_AFTER_TOGGLE = 'afterToggle',
        EVT_BEFORE_UPDATE_STATE = 'beforeUpdateState',
        EVT_AFTER_UPDATESATE = 'afterUpdateState',

        // Shortcuts, etc
        HOST = 'host',
        BOUNDING_BOX = 'boundingBox',
        CONTENT_BOX = 'contentBox',
        ROLE_ATTR = 'role',
        TAB_INDEX_ATTR = 'tabindex',
        ARIA_LABELLED_BY_ATTR = 'aria-labelledby',
        ARIA_DESCRIBED_BY_ATTR = 'aria-describedby',
        ARIA_HIDDEN_ATTR = 'aria-hidden',
        ARIA_EXPANDED_ATTR = 'aria-expanded',
        VALID_ARIA_STATES = [ARIA_HIDDEN_ATTR, ARIA_EXPANDED_ATTR],
        Lang = Y.Lang;

    var ARIACONTROLLED = function() {
        ARIACONTROLLED.superclass.constructor.apply(this, arguments);
    };

    Y.extend(ARIACONTROLLED, Y.Plugin.Base,
        {
            initializer: function() {
                // Initialize the role attribute on the host
                if (this.get('role') !== '') {
                    this.get('box').setAttribute(ROLE_ATTR, this.get('role'));
                }
                // Initialize the aria labelled by attribute on the host
                if (!Lang.isNull(this.get('ariaLabelledBy'))) {
                    this.get('box').setAttribute(ARIA_LABELLED_BY_ATTR, this.get('ariaLabelledBy').generateID());
                }
                // Initialize the aria described by attribute on the host
                if (!Lang.isNull(this.get('ariaDescribedBy'))) {
                    this.get('box').setAttribute(ARIA_DESCRIBED_BY_ATTR, this.get('ariaDescribedBy').generateID());
                }
                // Initialize the tab index attribute on the host
                if (!Lang.isNull(this.get('tabIndex'))) {
                    if (!this.get('isWidget')) {
                        this.get(HOST).setAttribute(TAB_INDEX_ATTR, this.get('tabIndex'));
                    } else {
                        this.get(HOST).set('tabIndex', this.get('tabIndex'));
                    }
                }
                // Ensure state is properly set
                this.update_state();
            },

            /**
             * Toggle visibility state
             */
            toggle_state: function() {
                this.fire(EVT_BEFORE_TOGGLE);
                if (this.get('visible')) {
                    this._set('visible', false);
                } else {
                    this._set('visible', true);
                }
                this.update_state();
                this.fire(EVT_AFTER_TOGGLE);
            },

            /**
             * Based on current visibility, update the state
             */
            update_state: function() {
                this.fire(EVT_BEFORE_UPDATE_STATE);
                this.update_aria_state();
                if (this.get('visible')) {
                    if (this.get('autoHideShow')) {
                        this.get(HOST).show();
                    }
                    if (this.get('autoFocus')) {
                        this.focus();
                    }
                } else {
                    if (this.get('autoHideShow')) {
                        this.get(HOST).hide();
                    }
                }
                this.fire(EVT_AFTER_UPDATESATE);
            },

            /**
             * Based on current visibility, update the aria state
             */
            update_aria_state: function() {
                var state = this.get('visible');
                if (this.get('ariaState') == ARIA_HIDDEN_ATTR) {
                    state = !(state);
                }
                this.get('box').setAttribute(this.get('ariaState'), state);
            },

            /**
             * Focus on our host
             */
            focus: function() {
                this.get(HOST).focus();
            },

            /**
             * Either set to null or a Y.Node instance
             * @param value
             * @return {*}
             */
            _setAssociatedNode: function(value) {
                if (value === null) {
                    return null;
                }
                return Y.one(value);
            },

            /**
             * Either must be null or can be resolved to a Node instance
             * @param value
             * @return {Boolean}
             */
            _validateAssociatedNode: function(value) {
                if (value === null) {
                    return true;
                }
                return (Y.one(value) instanceof Y.Node);
            },

            /**
             * Ensure that the state is known
             * @param value
             * @return {Boolean}
             */
            _validateAriaState: function(value) {
                return (Y.Array.indexOf(VALID_ARIA_STATES, value) !== -1);
            },

            /**
             * Allow null or a number
             * @param value
             * @returns {boolean}
             */
            _validateTabIndex: function(value) {
                return Lang.isNull(value) ? true : Lang.isNumber(value);
            }
        },
        {
            NAME: ARIACONTROLLEDNAME,
            NS: ARIACONTROLLEDNAME,
            ATTRS: {
                /**
                 * The role attribute to set on the host.
                 *
                 * If set to an empty string, then the role attribute will
                 * not be set.
                 *
                 * Defaults to the hosts role attribute value if it exists,
                 * otherwise defaults to region.
                 *
                 * Possible roles: http://oaa-accessibility.org/examples/roles/
                 */
                role: {
                    valueFn: function() {
                        if (this.get(HOST).hasAttribute('role')) {
                            return this.get(HOST).getAttribute('role');
                        }
                        return 'region';
                    },
                    validator: Lang.isString,
                    writeOnce: true
                },
                /**
                 * The Y.Node or CSS selector string of the element that
                 * labels the host.
                 *
                 * The host will get an aria-labelledby="ID" attribute.
                 *
                 * Examples: http://oaa-accessibility.org/examples/prop/165/
                 */
                ariaLabelledBy: {
                    value: null,
                    validator: '_validateAssociatedNode',
                    setter: '_setAssociatedNode',
                    writeOnce: true
                },
                /**
                 * The Y.Node or CSS selector string of the element that
                 * describes the host.
                 *
                 * The host will get an aria-describedby="ID" attribute.
                 *
                 * If the host is a widget, then the contentBox will be
                 * the default.
                 *
                 * Examples: http://oaa-accessibility.org/examples/prop/163/
                 */
                ariaDescribedBy: {
                    valueFn: function() {
                        if (this.get('isWidget')) {
                            return this.get(HOST).get(CONTENT_BOX);
                        }
                        return null;
                    },
                    validator: '_validateAssociatedNode',
                    setter: '_setAssociatedNode',
                    writeOnce: true
                },
                /**
                 * The aria state attribute to use.
                 *
                 * Example: http://oaa-accessibility.org/examples/state/141/
                 * Example: http://oaa-accessibility.org/examples/state/143/
                 */
                ariaState: {
                    value: 'aria-hidden',
                    validator: '_validateAriaState',
                    writeOnce: true
                },
                /**
                 * Automatically focus on the host when shown
                 */
                autoFocus: {
                    value: true,
                    validator: Lang.isBoolean
                },
                /**
                 * Automatically hide/show the host when visibility changes
                 */
                autoHideShow: {
                    value: true,
                    validator: Lang.isBoolean
                },
                /**
                 * If the host is visible or not
                 */
                visible: {
                    value: false,
                    validator: Lang.isBoolean,
                    writeOnce: true
                },
                /**
                 * Set the tab index for the host.  This helps with the
                 * host receiving focus when shown.
                 *
                 * The host will get an tabindex="X" attribute.
                 */
                tabIndex: {
                    value: -1,
                    validator: '_validateTabIndex',
                    writeOnce: true
                },
                /**
                 * If the host is a widget or not
                 */
                isWidget: {
                    readOnly: true,
                    valueFn: function() {
                        return (this.get(HOST) instanceof Y.Widget);
                    }
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
                        if (this.get('isWidget')) {
                            return this.get(HOST).get(BOUNDING_BOX);
                        }
                        return this.get(HOST);
                    }
                }
            }
        }
    );

    M.local_mr = M.local_mr || {};
    M.local_mr.ariacontrolled = ARIACONTROLLED;

}, '@VERSION@', {
    requires: ['plugin', 'widget']
});
