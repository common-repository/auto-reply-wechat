(function (blocks, element, editor) {
    var el = element.createElement;
    var RichText = editor.RichText;

    var iconUrl = url + '../image/weixingzh.jpg';

    blocks.registerBlockType('wechatreplay-ymz/wechatreplay-editable', {
        title: '验证码',
        icon: el('img', { src: iconUrl, alt: '验证码图标', style: { width: '20px', height: '20px' } }),
        category: 'layout',
        attributes: {
            content: {
                type: 'string',//内容类型为字符串
                source: 'html',//从HTML中提取内容
                selector: 'div', //选择器指定为<div>标签
            },
        },
        edit: function (props) {
            var content = props.attributes.content || '';

            // 仅在内容中不存在标签时，添加标签
            if (!content.startsWith('[WechatReplay]') || !content.endsWith('[/WechatReplay]')) {
                content = `[WechatReplay]${content}[/WechatReplay]`;
            }

            function onChangeContent(newContent) {
                // 确保用户输入的内容始终带有标签
                props.setAttributes({ content: `[WechatReplay]${newContent}[/WechatReplay]` });
            }

            return el(
                'div',
                { className: props.className },
                // 此处去掉 tagName，tagName指定什么标签，输入内容会包裹在指定标签之中
                el(RichText, {
                    className: 'my-custom-input',
                    onChange: onChangeContent,
                    value: content.replace(/^\[WechatReplay\]|(\[\/WechatReplay\])$/g, ''), // 移除标签以便用户编辑
                    placeholder: '-----验证码功能-----',
                })
            );
        },
        save: function (props) {
            var content = props.attributes.content || '';

            // 确保内容格式正确并去除多余的换行符
            content = content.replace(/<br\s*\/?>/g, '').trim();

            return el(
                'div',
                { className: props.className },
                // 直接返回已经带有标签的内容
                //  el(RichText.Content, {
                //  tagName: 'p', 这里tagName指定的标签和上面的要一致
                //  value: content
                // })
                //content  // 直接返回控制内容，避免使用 RichText.Content
                 el(RichText.Content, { value: content }) // 使用 RichText.Content
            );
        },
    });
}(
    window.wp.blocks,
    window.wp.element,
    window.wp.editor
));
