/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { 
    PanelBody, 
    PanelRow, 
    TextControl, 
    ToggleControl, 
    SelectControl,
    __experimentalNumberControl as NumberControl
} from '@wordpress/components';
import {
    InnerBlocks,
    ColorPalette,
    InspectorControls,
    useBlockProps
} from '@wordpress/block-editor';
import { useState } from 'react';

// Register the block
registerBlockType( 'yorickblom/input', {
    edit: ( { attributes, setAttributes } ) => {
        const blockProps = useBlockProps();
        
        return (<div>
            <InspectorControls key="setting">
                <PanelBody title="General Settings" initialOpen={ true }>
                    <PanelRow>
                        <SelectControl 
                            label="Typen"
                            value={attributes.field}
                            onChange={(value) => {
                                setAttributes({field: value})
                            }}
                            options={ [
                                { label: 'Basis input', value: 'input' },
                                { label: 'Tekt vak', value: 'textarea' },
                            ] }
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl 
                            label="Identifier"
                            value={attributes.name}
                            onChange={(value) => {
                                setAttributes({name: value})
                            }}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl 
                            label="Placeholder"
                            value={attributes.placeholder}
                            onChange={(value) => {
                                setAttributes({placeholder: value})
                            }}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl 
                            label="Default waarde"
                            value={attributes.default}
                            onChange={(value) => {
                                setAttributes({default: value})
                            }}
                        />
                    </PanelRow>
                    <PanelRow>
                        <ToggleControl 
                            label="Is hidden?"
                            checked={attributes.hidden}
                            onChange={ () => {
                                setAttributes({hidden: !attributes.hidden})
                            } }
                        />
                    </PanelRow>
                    <PanelRow>
                        <ToggleControl 
                            label="Is required?"
                            checked={attributes.required}
                            onChange={ () => {
                                setAttributes({required: !attributes.required})
                            } }
                        />
                    </PanelRow>
                </PanelBody>
                <PanelBody title="Input Settings" initialOpen={ false }>
                    <PanelRow>
                        <SelectControl 
                            label="Input type"
                            value={attributes.type}
                            options={ [
                                { label: 'Text', value: 'text' },
                                { label: 'Password', value: 'password' },
                                { label: 'E-mail', value: 'email' },
                                { label: 'Submit', value: 'submit' },
                            ] }
                            onChange={(value) => {
                                setAttributes({type: value})
                            }}
                        />
                    </PanelRow>
                </PanelBody>
                <PanelBody title="Textarea Settings" initialOpen={ false }>
                    <PanelRow>
                        <NumberControl
                            label="Aantal rijen"
                            value={attributes.rows}
                            onChange={ (value) => {
                                setAttributes({rows: value})
                            }}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
            {attributes.field == "input" ? (<input
                {...blockProps}
                type={attributes.type} 
                name={attributes.name} 
                id={attributes.id} 
                hidden={attributes.hidden} 
                required={attributes.required}
                value={attributes.default}
                placeholder={attributes.placeholder}
            />) : (<textarea 
                name={attributes.name} 
                id={attributes.id} 
                rows={attributes.rows}
                hidden={attributes.hidden} 
                required={attributes.required}
                value={attributes.default}
                placeholder={attributes.placeholder}
            />) }
        </div>);
    },
    save: ( { attributes, setAttributes } ) =>  {
        const blockProps = useBlockProps.save();

        if(attributes.field == "input") {
            return (<input
                {...blockProps}
                type={attributes.type} 
                name={attributes.name} 
                id={attributes.id} 
                hidden={attributes.hidden} 
                required={attributes.required}
                value={attributes.default}
                placeholder={attributes.placeholder}
            />);
        } else {
            return (<textarea 
                name={attributes.name} 
                id={attributes.id} 
                rows={attributes.rows}
                hidden={attributes.hidden} 
                required={attributes.required}
                value={attributes.default}
                placeholder={attributes.placeholder}
            />);
        }
    },
} );