import './styles/post.scss';

import 'tinymce';

tinymce.init({
    selector: 'textarea#new_post_message, textarea#edit_post_message',
    setup: function(editor) {
        editor.on('change', function (e) {
            editor.save();
        });
    }
});