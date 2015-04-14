!function ($) {

  "use strict";

  /* Collection PUBLIC CLASS DEFINITION
   * ============================== */

  var Collection = function(collection) {
    var $collection = $(collection);

    this.collectionSelector = '#' + $collection.attr('id');
    this.collectionId = $collection.data('collectionId');
    this.collectionItemsSelector = this.collectionSelector + ' .collection-items:first';
    this.collectionItemSelector = this.collectionItemsSelector + ' > .collection-item';

    $(this.collectionItemSelector).each(function(index) {
      $(this).attr('data-index', index);
    });

    this.index = $(this.collectionItemSelector).length - 1;
    this.show = $collection.data('show-animation');
    this.hide = $collection.data('hide-animation');
  };

  Collection.prototype = {
    constructor: Collection,
    add: function () {
      var self = this;
      function afterShow() {
        // apply plugins
        var replacementTokens = {};
        $item.parents('.collection-item').each(function() {
          var parentCollectionItem = $(this);

          var parentPrototypeName = parentCollectionItem.closest('[data-collection-id]').data('prototypeName');
          replacementTokens[parentPrototypeName] = parentCollectionItem.data('index');
        });
        replacementTokens[prototypeName] = self.index;
        SF.elements.apply($item, replacementTokens);

        $collection.trigger('ite-add.collection', [$item]);
      }

      this.index++;

      var $collection = $(this.collectionSelector);
      var prototypeName = $collection.data('prototypeName');
      if ('undefined' === typeof prototypeName) {
        prototypeName = '__name__';
      }

      var re = new RegExp(prototypeName, 'g');
      var itemHtml = $collection.data('prototype').replace(re, this.index);
      var $item = $(itemHtml).attr('data-index', this.index);

      var event = $.Event('ite-before-add.collection');
      $collection.trigger(event, [$item]);
      if (false === event.result) {
        return;
      }

      $item.hide();
      $(this.collectionItemsSelector).append($item);

      switch (this.show.type.toLowerCase()) {
        case 'fade':
          $item.fadeIn(this.show.length, afterShow);
          break;
        case 'slide':
          $item.slideDown(this.show.length, afterShow);
          break;
        case 'show':
          $item.show(this.show.length, afterShow);
          break;
        default:
          $item.show(null, afterShow);
          break;
      }
    },
    remove: function ($btn) {
      var self = this;
      function afterHide() {
        $item.remove();

        $collection.trigger('ite-remove.collection', [$item]);
      }

      if (0 !== $btn.parents('.collection-item').length) {
        var $item = $btn.closest('.collection-item');
        var $collection = $(this.collectionSelector);

        var event = $.Event('ite-before-remove.collection');
        $collection.trigger(event, [$item]);
        if (false === event.result) {
          return;
        }

        switch (this.hide.type.toLowerCase()) {
          case 'fade':
            $item.fadeOut(this.hide.length, afterHide);
            break;
          case 'slide':
            $item.slideUp(this.hide.length, afterHide);
            break;
          case 'hide':
            $item.hide(this.hide.length, afterHide);
            break;
          default:
            $item.hide(null, afterHide);
            break;
        }
      }
    },
    items: function() {
      return $(this.collectionItemSelector);
    },
    itemsCount: function() {
      return this.items().length;
    },
    parents: function() {
      return $(this.collectionSelector).parents('[data-collection-id]');
    },
    parentsCount: function() {
      return this.parents().length;
    },
    hasParent: function() {
      return 0 !== this.parentsCount().length;
    },
    itemsWrapper: function() {
      return $(this.collectionItemsSelector);
    }
  };


  /* COLLECTION PLUGIN DEFINITION
   * ======================== */

  $.fn.collection = function(method) {
    var methodArguments = arguments, value;
    this.each(function() {
      var $this = $(this);

      var data = $this.data('collection');
      if (!data) {
        $this.data('collection', (data = new Collection(this)));
      }
      if ($.isFunction(data[method])) {
        value = data[method].apply(data, Array.prototype.slice.call(methodArguments, 1));
      } else {
        $.error('Method with name "' +  method + '" does not exist in jQuery.collection');
      }
    });
    return ('undefined' === typeof value) ? this : value;
  };

  $.fn.collection.Constructor = Collection;


  /* COLLECTION DATA-API
   * =============== */

  $(function () {
    // add
    $('body').on('click.collection', '[data-collection-add-btn]', function (e) {
      var $btn = $(this);
      var $collection = $btn.data('collectionAddBtn')
        ? $($btn.data('collectionAddBtn'))
        : $btn.closest('[data-collection-id]');
      if (!$collection.length) {
        return;
      }

      $collection.collection('add');
      e.preventDefault();
    });

    // remove
    $('body').on('click.collection', '[data-collection-remove-btn]', function (e) {
      var $btn = $(this);
      //var $row = $btn.data('collectionRemoveBtn')
      //  ? $($btn.data('collectionRemoveBtn'))
      //  : $btn.closest('.collection-item');
      var $collection = $btn.data('collectionRemoveBtn')
        ? $($btn.data('collectionRemoveBtn'))
        : $btn.closest('[data-collection-id]');

      if (!$collection.length) {
        return;
      }

      $collection.collection('remove', $btn);
      e.preventDefault();
    });
  });

}(window.jQuery);