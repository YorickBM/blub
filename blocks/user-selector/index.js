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
registerBlockType( 'yorickblom/user-selector', {
    edit: ( { attributes, setAttributes } ) => {
        const blockProps = useBlockProps();

        return (<div {...blockProps}>
            <select name="users[]" multiple="multiple">
                <option>User #1</option>
                <option>User #2</option>
                <option>User #3</option>
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