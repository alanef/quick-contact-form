var el = wp.element.createElement,
	registerBlockType = wp.blocks.registerBlockType,
	ServerSideRender = wp.components.ServerSideRender,
	TextControl = wp.components.TextControl,
	RadioControl = wp.components.RadioControl,
    SelectControl = wp.components.SelectControl,
	TextareaControl = wp.components.TextareaControl,
	CheckboxControl = wp.components.CheckboxControl,
	InspectorControls = wp.editor.InspectorControls;

registerBlockType( 'quick-contact-form/block', {
	title: 'Contact Form',
	icon: 'email',
	category: 'widgets',
	edit: function( props ) {		
		return [
			el( 'h2', // Tag type.
					{
						className: props.className,
					},
					'Quick Contact Form'
				),
		];
	},
	save: function() {
		return null;
	},
} );