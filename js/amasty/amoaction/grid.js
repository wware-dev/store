
var amoaction = Class.create(varienGridMassaction, {
    
    apply: function($super) {
        var fields = ['carrier', 'tracking'];
        
        for (var i=0; i < fields.length; ++i){
            var vals = [];
            $$('.amasty-' + fields[i]).each(function(s) {
                vals.push (s.readAttribute('rel')+'|'+s.value);
            });        
            new Insertion.Bottom(this.formAdditional, this.fieldTemplate.evaluate({name: fields[i], value: vals}));
        }
        
        return $super();
    }
    
});