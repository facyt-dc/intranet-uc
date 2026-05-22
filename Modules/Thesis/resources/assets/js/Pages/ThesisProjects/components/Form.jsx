// En tu componente Form.jsx

import React, { useRef, useState } from "react";
import { useForm } from '@inertiajs/react';

// --- IMPORTS COMPLETOS DE MATERIAL-UI ---
// Este bloque ahora incluye TODOS los componentes y iconos necesarios.
import Dialog from '@mui/material/Dialog'
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogContentText from '@mui/material/DialogContentText';
import DialogTitle from '@mui/material/DialogTitle';
import Box from '@mui/material/Box';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import Autocomplete from '@mui/material/Autocomplete';
import Grid from '@mui/material/Grid';
import Typography from '@mui/material/Typography';
import IconButton from '@mui/material/IconButton';
import Link from '@mui/material/Link'; // Necesario para el enlace de descarga

// Iconos
import CloudUploadIcon from '@mui/icons-material/CloudUpload';
import ClearIcon from '@mui/icons-material/Clear';
import AttachFileIcon from '@mui/icons-material/AttachFile';

export default function Form({ thesis, routeName, students, teachers, method = 'post', onCancel }) {
    const isEdit = !!thesis;

    // Helper para encontrar archivos existentes por tipo
    const findExistingFile = (type) => thesis?.files?.find(f => f.type === type);
    
    const existingPteg = findExistingFile('pteg');
    const existingTeg = findExistingFile('teg');

    const { data, setData, post, processing, errors } = useForm({
        title: thesis?.title ?? '',
        date: thesis?.date ?? '',
        student_ids: thesis?.students?.map(s => s.id) ?? [],
        teacher_ids: thesis?.teachers?.map(t => t.id) ?? [],
        
        // Inputs para NUEVOS archivos
        pteg_document: null,
        teg_document: null,

        // Array para marcar archivos existentes para BORRAR
        deleted_files: [],

        _method: method, // 'POST' para crear, 'PATCH' para editar

        transform: (data) => {
            // Preparamos el payload final para el backend
            return {
                ...data,
                // 'files' será un array asociativo que el backend puede iterar
                files: {
                    pteg: data.pteg_document,
                    teg: data.teg_document,
                },
                pteg_document: undefined,
                teg_document: undefined,
            };
        },
    });

    const ptegRef = useRef();
    const tegRef = useRef();

    const [confirmationOpen, setConfirmationOpen] = useState(false);
    const [pendingTegFile, setPendingTegFile] = useState(null);

    const handleTegFileSelect = (e) => {
        if (e.target.files.length > 0) {
            // No establecemos el archivo en el formulario todavía.
            // Lo guardamos en un estado temporal y abrimos el diálogo.
            setPendingTegFile(e.target.files[0]);
            setConfirmationOpen(true);
        }
        // Limpiamos el valor del input para que el evento 'onChange' se dispare de nuevo si se selecciona el mismo archivo
        e.target.value = null; 
    };

    const handleConfirmUpload = () => {
        // Si el usuario confirma, ahora sí establecemos el archivo en el formulario.
        if (pendingTegFile) {
            setData('teg_document', pendingTegFile);
        }
        handleCloseConfirmation();
    };

    const handleCloseConfirmation = () => {
        setConfirmationOpen(false);
        setPendingTegFile(null);
    };

    const handleChange = (e) => setData(e.target.name, e.target.value);
    const handleStudentsChange = (event, value) => setData('student_ids', value.map(s => s.id));
    const handleTeachersChange = (event, value) => setData('teacher_ids', value.map(t => t.id));


     const handlePtegFileChange = (e) => {
        if (e.target.files.length > 0) {
            setData('pteg_document', e.target.files[0]);
        }
    };

const handleDeleteExistingFile = (fileId, fieldName) => {
    // Le pasamos a setData una función que recibe el estado actual ('currentData')
    setData(currentData => {
        // Creamos una nueva copia del array de archivos a borrar
        const newDeletedFiles = currentData.deleted_files.includes(fileId)
            ? currentData.deleted_files
            : [...currentData.deleted_files, fileId];

        // Devolvemos un objeto de estado completamente nuevo
        return {
            ...currentData, // Copiamos todas las propiedades antiguas
            deleted_files: newDeletedFiles, // Sobrescribimos con el nuevo array
            [fieldName]: null, // Usamos computed property para actualizar el campo correcto (pteg_document o teg_document)
        };
    });
};

    const handleSubmit = (e) => {
        e.preventDefault();
        const targetRoute = isEdit ? route(routeName, thesis.id) : route(routeName);
        post(targetRoute, {
            preserveScroll: true,
        });
    };
    
    // Componente para renderizar la info de un archivo existente
    const renderExistingFileInfo = (file, fieldName) => {
        if (data.deleted_files.includes(file.id)) return null;

        return (
            <Box display="flex" alignItems="center" mt={1} p={1} borderRadius={1} bgcolor="grey.200">
                <AttachFileIcon fontSize="small" color="action" />
                {/* Asegúrate que la ruta de storage sea correcta */}
                <Link  href={route('thesis-files.download', file.id)} 
                sx={{ ml: 1, flexGrow: 1, textDecoration: 'none', color: 'inherit', cursor: 'pointer' }}>
                    <Typography variant="body2" noWrap>{file.original_name}</Typography>
                </Link>
                <IconButton size="small" onClick={() => handleDeleteExistingFile(file.id, fieldName)}><ClearIcon fontSize="small" /></IconButton>
            </Box>
        );
    };

    // Componente para renderizar la info de un NUEVO archivo seleccionado
    const renderNewFileInfo = (file, fieldName) => {
        if (!file) return null;
        return (
            <Box display="flex" alignItems="center" mt={1} p={1} borderRadius={1} bgcolor="grey.100">
                <AttachFileIcon fontSize="small" color="action" />
                <Typography variant="body2" sx={{ ml: 1, flexGrow: 1 }} noWrap>{file.name}</Typography>
                <IconButton size="small" onClick={() => setData(fieldName, null)}><ClearIcon fontSize="small" /></IconButton>
            </Box>
        );
    };

    return (
         <> 
        <Box component="form" sx={{ mt: 2 }} onSubmit={handleSubmit} id="thesisForm" encType="multipart/form-data">
            <Grid container spacing={3}>
                {/* --- SECCIÓN DE CAMPOS DE TEXTO Y AUTOCOMPLETE --- */}
                <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>Título</Typography>
                    <TextField required fullWidth size="small" name="title" value={data.title} onChange={handleChange} error={!!errors.title} helperText={errors.title} />
                </Grid>
                <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>Fecha</Typography>
                    <TextField required fullWidth size="small" type="date" name="date" value={data.date} onChange={handleChange} error={!!errors.date} helperText={errors.date} InputLabelProps={{ shrink: true }} />
                </Grid>
                <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>Tesistas</Typography>
                    <Autocomplete 
                        multiple 
                        options={students} 
                        getOptionLabel={(option) => `${option.name} (${option.id_uc || 'N/A'})`} 
                        value={students.filter(s => data.student_ids.includes(s.id))} 
                        onChange={handleStudentsChange} 
                        isOptionEqualToValue={(option, value) => option.id === value.id} 
                        limitTags={2} 
                        renderInput={(params) => (<TextField {...params} size="small" error={!!errors.student_ids} helperText={errors.student_ids} />)} 
                    />
                </Grid>

                <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>Tutor(es)</Typography>
                    <Autocomplete 
                        multiple 
                        options={teachers} 
                        getOptionLabel={(option) => option.name} 
                        value={teachers.filter(t => data.teacher_ids.includes(t.id))} 
                        onChange={handleTeachersChange} 
                        isOptionEqualToValue={(option, value) => option.id === value.id} 
                        limitTags={2} 
                        renderInput={(params) => (<TextField {...params} size="small" error={!!errors.teacher_ids} helperText={errors.teacher_ids} />)} 
                    />
                </Grid>

                {/* --- SECCIÓN DE ARCHIVOS --- */}
                <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>Documento PTEG</Typography>
                    {data.pteg_document 
                        ? renderNewFileInfo(data.pteg_document, 'pteg_document') 
                        : existingPteg && renderExistingFileInfo(existingPteg, 'pteg_document')
                    }
                    <input type="file" name="pteg_document" ref={ptegRef} onChange={handlePtegFileChange} style={{ display: 'none' }} accept=".pdf,.doc,.docx,.zip" />
                    <Button sx={{mt: 1}} variant="outlined" startIcon={<CloudUploadIcon />} onClick={() => ptegRef.current.click()}>
                        {existingPteg && !data.deleted_files.includes(existingPteg.id) && !data.pteg_document ? 'Reemplazar' : 'Seleccionar archivo'}
                    </Button>
                    {errors['files.pteg'] && <Typography color="error" variant="caption">{errors['files.pteg']}</Typography>}
                </Grid>

                <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>Documento TEG</Typography>
                    {data.teg_document
                        ? renderNewFileInfo(data.teg_document, 'teg_document')
                        : existingTeg && renderExistingFileInfo(existingTeg, 'teg_document')
                    }
                    <input type="file" name="teg_document" ref={tegRef} onChange={handleTegFileSelect} style={{ display: 'none' }} accept=".pdf,.doc,.docx,.zip" />
                    <Button sx={{mt: 1}} variant="outlined" startIcon={<CloudUploadIcon />} onClick={() => tegRef.current.click()}>
                        {existingTeg && !data.deleted_files.includes(existingTeg.id) && !data.teg_document ? 'Reemplazar' : 'Seleccionar archivo'}
                    </Button>
                    {errors['files.teg'] && <Typography color="error" variant="caption">{errors['files.teg']}</Typography>}
                </Grid>
            </Grid>

            {/* --- SECCIÓN DE BOTONES DE ACCIÓN --- */}
            <Box display="flex" gap={2} mt={5}>
                <Button variant="contained" type="submit" disabled={processing}>{isEdit ? 'Actualizar' : 'Crear'}</Button>
                {onCancel && <Button variant="outlined" color="inherit" onClick={onCancel}>Cancelar</Button>}
            </Box>
        </Box>
         <Dialog open={confirmationOpen} onClose={handleCloseConfirmation}>
                <DialogTitle>Confirmación de Cambio de Estado</DialogTitle>
                <DialogContent>
                    <DialogContentText>
                        Al subir un documento TEG, el estado de los estudiantes asociados a este proyecto cambiará a "TEG inscrito".
                        <br /><br />
                        ¿Está seguro de que desea continuar?
                    </DialogContentText>
                </DialogContent>
                <DialogActions>
                    <Button onClick={handleCloseConfirmation} color="inherit">Cancelar</Button>
                    <Button onClick={handleConfirmUpload} variant="contained" autoFocus>
                        Sí, subir y cambiar estado
                    </Button>
                </DialogActions>
            </Dialog>
        </>
    );
}