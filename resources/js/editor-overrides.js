export default (editor, opts = {}) => {
    const domc = editor.Components;

    // Helper common traits
    const commonTraits = [
        { name: 'id', label: 'ID', placeholder: 'my-element-id' },
        { name: 'title', label: 'Title (Tooltip)', placeholder: 'Hover text' },
        { name: 'class', label: 'Extra Classes', placeholder: 'class1 class2' }
    ];

    // -- Link --
    // We extend 'link' to keep existing logic (like preventing default click)
    domc.addType('link', {
        extend: 'link',
        model: {
            defaults: {
                traits: [
                    { name: 'href', label: 'URL (Href)', placeholder: 'https://...' },
                    {
                        type: 'select',
                        name: 'target',
                        label: 'Open in',
                        options: [
                            { value: '', name: 'Same Tab' },
                            { value: '_blank', name: 'New Tab' }
                        ]
                    },
                    { name: 'rel', label: 'Rel Attribute', placeholder: 'nofollow' },
                    ...commonTraits
                ]
            }
        }
    });

    // -- Image --
    domc.addType('image', {
        extend: 'image',
        isComponent: el => {
            if (el.tagName === 'IMG') return { type: 'image' };
        },
        model: {
            defaults: {
                traits: [
                    { name: 'src', label: 'Source (URL)' },
                    { name: 'alt', label: 'Alt Text' },
                    { name: 'title', label: 'Title' },
                    {
                        type: 'select',
                        name: 'loading',
                        label: 'Lazy Load',
                        options: [
                            { value: 'lazy', name: 'Yes (Lazy)' },
                            { value: 'eager', name: 'No (Eager)' }
                        ]
                    },
                    ...commonTraits
                ]
            }
        }
    });

    // -- Form --
    // We explicitly define this to ensuring traits are visible even with the plugin installed
    domc.addType('form', {
        extend: 'default',
        isComponent: el => {
            if (el.tagName === 'FORM') return { type: 'form' };
        },
        model: {
            defaults: {
                tagName: 'form',
                traits: [
                    { name: 'action', label: 'Action URL', placeholder: '/submit-form' },
                    {
                        type: 'select',
                        name: 'method',
                        label: 'Method',
                        options: [
                            { value: 'POST', name: 'POST' },
                            { value: 'GET', name: 'GET' }
                        ]
                    },
                    { name: 'name', label: 'Form Name' },
                    ...commonTraits
                ]
            }
        }
    });

    // -- Input --
    domc.addType('input', {
        extend: 'default',
        isComponent: el => {
            if (el.tagName === 'INPUT') return { type: 'input' };
        },
        model: {
            defaults: {
                tagName: 'input',
                traits: [
                    { name: 'name', label: 'Input Name', placeholder: 'email' },
                    {
                        type: 'select',
                        name: 'type',
                        label: 'Type',
                        options: [
                            { value: 'text', name: 'Text' },
                            { value: 'email', name: 'Email' },
                            { value: 'password', name: 'Password' },
                            { value: 'number', name: 'Number' },
                            { value: 'checkbox', name: 'Checkbox' },
                            { value: 'radio', name: 'Radio' },
                            { value: 'date', name: 'Date' },
                            { value: 'hidden', name: 'Hidden' }
                        ]
                    },
                    { name: 'placeholder', label: 'Placeholder' },
                    { name: 'value', label: 'Value' },
                    { type: 'checkbox', name: 'required', label: 'Required' },
                    { type: 'checkbox', name: 'checked', label: 'Checked (Radio/Box)' },
                    ...commonTraits
                ]
            }
        }
    });

    // -- Button --
    domc.addType('button', {
        extend: 'default',
        isComponent: el => {
            if (el.tagName === 'BUTTON') return { type: 'button' };
        },
        model: {
            defaults: {
                tagName: 'button',
                traits: [
                    { name: 'type', type: 'select', options: [{ value: 'submit', name: 'Submit' }, { value: 'button', name: 'Button' }, { value: 'reset', name: 'Reset' }] },
                    { name: 'name', label: 'Name' },
                    { name: 'value', label: 'Value' },
                    ...commonTraits
                ]
            }
        }
    });

    // -- Textarea --
    domc.addType('textarea', {
        extend: 'default',
        isComponent: el => {
            if (el.tagName === 'TEXTAREA') return { type: 'textarea' };
        },
        model: {
            defaults: {
                tagName: 'textarea',
                traits: [
                    { name: 'name', label: 'Name' },
                    { name: 'placeholder', label: 'Placeholder' },
                    { name: 'rows', label: 'Rows', type: 'number' },
                    { type: 'checkbox', name: 'required', label: 'Required' },
                    ...commonTraits
                ]
            }
        }
    });
};
