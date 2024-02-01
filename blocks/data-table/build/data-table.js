!function(){"use strict";var e=window.React,t=window.wp.blocks,l=window.wp.components,n=window.wp.blockEditor;(0,t.registerBlockType)("yorickblom/data-table",{edit:({attributes:t,setAttributes:a})=>{const o=(0,n.useBlockProps)(),r=t.columns.split(",");return(0,e.createElement)("table",{id:t.id,"data-url":t.url,"data-json":t.post,"data-cols":t.json,...o},(0,e.createElement)(n.InspectorControls,{key:"setting"},(0,e.createElement)(l.PanelBody,{title:"Data settings"},(0,e.createElement)(l.PanelRow,null,(0,e.createElement)(l.TextControl,{label:"Tabel ID (Vereist unique te zijn)",value:t.id,onChange:e=>a({id:e})})),(0,e.createElement)(l.PanelRow,null,(0,e.createElement)(l.TextControl,{label:"Tabel kolommen",help:"Meerdere kolommen scheiden met een comma.",value:t.columns,onChange:e=>a({columns:e})})),(0,e.createElement)(l.PanelRow,null,(0,e.createElement)(l.TextControl,{label:"Kolommen data",help:"Json data naam voor kolom.",value:t.json,onChange:e=>a({json:e})}))),(0,e.createElement)(l.PanelBody,{title:"Data endpoint"},(0,e.createElement)(l.PanelRow,null,(0,e.createElement)(l.TextControl,{label:"Endpoint URL",value:t.url,onChange:e=>a({url:e})})),(0,e.createElement)(l.PanelRow,null,(0,e.createElement)(l.TextareaControl,{label:"Endpoint data",help:"Enter valid JSON, if empty GET method is used.",value:t.post,onChange:e=>a({post:e})})))),(0,e.createElement)("thead",null,(0,e.createElement)("tr",null,r.map(((t,l)=>(0,e.createElement)("th",null,t))))))},save:({attributes:t,setAttributes:l})=>{const a=n.useBlockProps.save(),o=t.columns.split(",");return(0,e.createElement)("table",{id:t.id,"data-url":t.url,"data-json":t.post,"data-cols":t.json,...a},(0,e.createElement)("thead",null,(0,e.createElement)("tr",null,o.map(((t,l)=>(0,e.createElement)("th",null,t))))))}})}();