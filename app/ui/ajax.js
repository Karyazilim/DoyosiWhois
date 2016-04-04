var ajaxManager = (function() {
     var requests = [];

     return {
        addReq:  function(opt) {
            requests.push(opt);
        },
        removeReq:  function(opt) {
            if( $.inArray(opt, requests) > -1 )
                requests.splice($.inArray(opt, requests), 1);
        },
        run: function() {
            var self = this,
                oriSuc;

            if( requests.length ) {
                oriSuc = requests[0].complete;

                requests[0].complete = function() {
                     if( typeof(oriSuc) === 'function' ) oriSuc();
                     requests.shift();
                     self.run.apply(self, []);
                };

                $.ajax(requests[0]);
            } else {
              self.tid = setTimeout(function() {
                 self.run.apply(self, []);
              }, 1000);
            }
        },
        stop:  function() {
            requests = [];
            clearTimeout(this.tid);
        }
     };
}());