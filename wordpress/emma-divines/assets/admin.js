/* Photo pickers for Emma Divines fields */
jQuery(function($){
  $(document).on('click','.ed-media-pick',function(e){
    e.preventDefault();
    var box=$(this).closest('.ed-media'),multi=box.data('multiple')==1,input=box.find('input[type=hidden]');
    var frame=wp.media({title:multi?'Choose photos':'Choose a photo',multiple:multi?'add':false,library:{type:'image'},button:{text:'Use '+(multi?'these photos':'this photo')}});
    frame.on('open',function(){
      var sel=frame.state().get('selection');
      (input.val()||'').split(',').filter(Boolean).forEach(function(id){var a=wp.media.attachment(id);a.fetch();sel.add(a);});
    });
    frame.on('select',function(){
      var items=frame.state().get('selection').toJSON(),thumbs=box.find('.ed-media-thumbs').empty();
      input.val(items.map(function(a){return a.id;}).join(','));
      items.forEach(function(a){var s=(a.sizes&&a.sizes.thumbnail)?a.sizes.thumbnail.url:a.url;thumbs.append($('<img alt="">').attr('src',s));});
      box.find('.ed-media-clear').prop('hidden',!items.length);
    });
    frame.open();
  });
  $(document).on('click','.ed-media-clear',function(e){
    e.preventDefault();var box=$(this).closest('.ed-media');
    box.find('input[type=hidden]').val('');box.find('.ed-media-thumbs').empty();$(this).prop('hidden',true);
  });
});
