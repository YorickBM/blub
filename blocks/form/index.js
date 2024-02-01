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
registerBlockType( 'yorickblom/form', {
    edit: ( { attributes, setAttributes } ) => {
        const blockProps = useBlockProps();
        const onChangeAction = (value) => {
            setAttributes({action: value});
        }

        return (<div {...blockProps} method="post">
            <InspectorControls key="setting">
                <PanelBody title="PHP Actie">
                    <PanelRow>
                        <TextControl 
                            value={attributes.action}
                            onChange={onChangeAction}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
            <InnerBlocks />
        </div>);
    },
    save: ( { attributes, setAttributes } ) =>  {
        const blockProps = useBlockProps.save();

        return (<form {...blockProps} method="post"> 
            <InnerBlocks.Content />
             </form>);
    },
} );