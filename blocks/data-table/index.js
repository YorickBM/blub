/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { 
    PanelBody, 
    PanelRow, 
    TextControl, 
    TextareaControl 
} from '@wordpress/components';
import {
    InnerBlocks,
    ColorPalette,
    InspectorControls,
    useBlockProps
} from '@wordpress/block-editor';

// Register the block
registerBlockType( 'yorickblom/data-table', {
    edit: ( { attributes, setAttributes } ) => {
        const blockProps = useBlockProps();
        const columns = attributes.columns.split(',');

        return (<table id={attributes.id} data-url={attributes.url} data-json={attributes.post} data-cols={attributes.json} {...blockProps}>
            <InspectorControls key="setting">
                <PanelBody title="Data settings">
                    <PanelRow>
                        <TextControl 
                            label="Tabel ID (Vereist unique te zijn)"
                            value={ attributes.id }
                            onChange={ (value) => setAttributes({id: value}) }
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl 
                            label="Tabel kolommen"
                            help="Meerdere kolommen scheiden met een comma."
                            value={ attributes.columns }
                            onChange={ (value) => setAttributes({columns: value}) }
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl 
                            label="Kolommen data"
                            help="Json data naam voor kolom."
                            value={ attributes.json }
                            onChange={ (value) => setAttributes({json: value}) }
                        />
                    </PanelRow>
                </PanelBody>
                <PanelBody title="Data endpoint">
                    <PanelRow>
                        <TextControl 
                            label="Endpoint URL"
                            value={ attributes.url }
                            onChange={ (value) => setAttributes({url: value}) }
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextareaControl 
                            label="Endpoint data"
                            help="Enter valid JSON, if empty GET method is used."
                            value={ attributes.post }
                            onChange={ (value) => setAttributes({post: value}) }
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
            <thead>
                <tr>
                    {columns.map((value, index) => (
                        <th>{value}</th>
                    ))}
                </tr>
            </thead>
        </table>);
    },
    save: ( { attributes, setAttributes } ) =>  {
        const blockProps = useBlockProps.save();
        const columns = attributes.columns.split(',');

        return (<table id={attributes.id} data-url={attributes.url} data-json={attributes.post} data-cols={attributes.json} {...blockProps} > 
            <thead>
                <tr>
                    {columns.map((value, index) => (
                        <th>{value}</th>
                    ))}
                </tr>
            </thead>
        </table>);
    },
} );