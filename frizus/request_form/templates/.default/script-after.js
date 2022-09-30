(function ($, window, document) {
    var pluginName = 'frizusRequestForm',
        defaults = {
            namespace: pluginName,
            orderListSelector: '.order-list',
            orderItemSelector: '.order-item',
            actionsContainerSelector: '.actions',
            addActionSelector: '.add',
            deleteActionSelector: '.delete',
        }

    function Plugin(element, options) {
        this.element = $(element)
        this.$elem = this.element
        this._name = pluginName
        this.settings = $.extend({}, defaults, options)
        this._defaults = defaults

        return this.init()
    }

    Plugin.prototype = {
        options: function (option, val) {
            this.settings[option] = val
        },
        destroy: function () {
            $.removeData(this.element, this._name)
        },
        init: function () {
            var plugin = this

            this.children = {
                $orderList: this.$elem.find(this.settings.orderListSelector)
            }

            this.children.$orderList.find(this.settings.orderItemSelector).each(function () {
                plugin.bindActions($(this))
            })

            this.maxOrderItemIndex = this.getMaxOrderItem()

            return this
        },
        getMaxOrderItem: function () {
            var $orderItem = this.children.$orderList.find(this.settings.orderItemSelector).last()
            var match = $orderItem.find(':input').first().attr('name').match(/^([^\[]+)\[(\d+)\]\[([^\]]+)\]$/)
            return parseInt(match[2]) + 1
        },
        replaceOrderInputIndex($input, newIndex) {
            var newName = $input.attr('name').replace(/^([^\[]+\[)\d+(\]\[[^\]]+\])$/, '$1' + newIndex + '$2')
            $input.attr('name', newName)
        },
        bindActions: function ($orderItem) {
            var plugin = this

            var $actionsContainer = $orderItem.find(this.settings.actionsContainerSelector)
            $actionsContainer.find(this.settings.addActionSelector).each(function () {
                $(this).bind('click.' + plugin.settings.namespace, $.proxy(plugin.addAction, plugin, this))
            })
            $actionsContainer.find(this.settings.deleteActionSelector).each(function () {
                $(this).bind('click.' + plugin.settings.namespace, $.proxy(plugin.deleteAction, plugin, this))
            })
        },
        addAction: function ($action, e) {
            e.preventDefault()

            var plugin = this

            var $orderItem = this.children.$orderList.find(this.settings.orderItemSelector).first().clone()
            $orderItem.find(':input').each(function () {
                $(this).val('')
                plugin.replaceOrderInputIndex($(this), plugin.maxOrderItemIndex)
            })
            this.maxOrderItemIndex++

            this.bindActions($orderItem)
            $orderItem.insertAfter($action.closest(this.settings.orderItemSelector))
        },
        deleteAction: function ($action, e) {
            e.preventDefault()

            if (this.children.$orderList.find(this.settings.orderItemSelector).length > 1) {
                $action.closest(this.settings.orderItemSelector).remove()
            }
        }
    }
    $.fn[pluginName] = function (options) {
        var args = $.makeArray(arguments),
            after = args.slice(1),
            methodCall = typeof options === 'string',
            methodResult = undefined,
            first = true

        var eachResult = this.each(function () {
            var instance = $.data(this, pluginName)

            if (instance) {
                if (instance[options]) {
                    if (first) {
                        methodResult = instance[options].apply(instance, after)
                    }
                    instance[options].apply(instance, after)
                } else {
                    //$.error('Method ' + options + ' does not exist on Plugin');
                }
            } else {
                var plugin = new Plugin(this, options)

                $.data(this, pluginName, plugin)
            }

            if (first) {
                first = false
            }
        })

        if (methodCall) {
            return methodResult
        } else {
            return eachResult
        }
    }
    $.fn[pluginName].prototype.defaults = defaults
    $.fn[pluginName].prototype.methods = Plugin.prototype
})(jQuery, window, document)