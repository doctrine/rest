var jActiveResource = jQuery.extend({
  create: function() {
    return jQuery.extend(this);
  },

  define: function (name, definition) {
    var instance = jQuery.extend(this, definition);
    eval(name + ' = instance;');
    eval(name + 'Factory = jQuery.extend(jActiveResourceFactory);')
  },

  find: function(id, callback) {
    this.execute('get', this.getUrl(id), null, callback);
  },

  findAll: function(data, callback) {
    this.execute('get', this.getUrl(), data, callback);
  },

  destroy: function(id, callback) {
    this.execute('delete', this.getUrl(this.id), null, callback);
  },

  save: function(callback) {
    this.execute('post', this.getUrl(this.id), this.toString(), callback);
  },

  getUrl: function(id, action, parameters) {
    if (id) {
      return this.url + '/' + id + '.json';
    } else {
      return this.url + '.json';
    }
  },

  execute: function(method, url, data, callback) {
    $.ajax({
      type: method,
      dataType: 'json',
      url: url,
      data: data,
      success: function(data) {
        if (data.length > 0) {
          var results = new Array();
          for (i = 0; i < data.length; i++) {
            results[i] = jQuery.extend(this.prototype, data[i]);
          }
          callback(results)
        } else {
          callback(jQuery.extend(this.prototype, data));
        }
      }
    });
  }
});

var jActiveResourceFactory = jActiveResource.extend({
  destroy: function(id, callback) {
    this.execute('delete', this.getUrl(id), null, callback);
  }
});