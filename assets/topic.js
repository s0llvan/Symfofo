import './styles/topic.scss';

import 'tinymce';

tinymce.init({
    selector: 'textarea#new_topic_message',
    setup: function(editor) {
        editor.on('change', function (e) {
            editor.save();
        });
    }
});