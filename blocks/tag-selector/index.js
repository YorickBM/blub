/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody, PanelRow, TextControl, ServerSideRender } from '@wordpress/components';
import {
    InnerBlocks,
    ColorPalette,
    InspectorControls,
    useBlockProps
} from '@wordpress/block-editor';

// Register the block
registerBlockType( 'yorickblom/tag-selector', {
    edit: ( { attributes, setAttributes } ) => {
        const blockProps = useBlockProps();

        return (<div {...blockProps}>
            <select name="tags[]" multiple="multiple">
                <option>Tag #1</option>
                <option>Tag #2</option>
                <option>Tag #3</option>
            </select>
        </div>);
    },
    save: ( { attributes, setAttributes } ) =>  {
        const blockProps = useBlockProps.save();

        return (<div {...blockProps}> 
                <select/>
             </div>);
    },
} );