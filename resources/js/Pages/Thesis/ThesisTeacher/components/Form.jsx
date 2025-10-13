import React from "react";
import { useForm } from '@inertiajs/react';
import Box from '@mui/material/Box';
import TextField from '@mui/material/TextField';
import Grid from '@mui/material/Grid';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';

export default function Form({ teacher, routeName, method = 'post', onCancel }) {
    const isEdit = !!teacher;

    const { data, setData, post, patch, processing, errors } = useForm({
        name: teacher?.name ?? '',
        email: teacher?.email ?? '',
        id_uc: teacher?.id_uc ?? '',
        ci: teacher?.ci ?? '',
    });

    const handleChange = (e) => {
        setData(e.target.name, e.target.value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        const targetRoute = isEdit ? route(routeName, teacher.id) : route(routeName);
        
        if (method === 'patch') {
            patch(targetRoute);
        } else {
            post(targetRoute);
        }
    };

    return (
        <Box component="form" sx={{ mt: 2 }} onSubmit={handleSubmit} id="teacherForm" autoComplete="off">
            <Grid container spacing={3}>
                <Grid item xs={12} sm={6}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>Nombre Completo</Typography>
                    <TextField required name="name" value={data.name} onChange={handleChange} error={!!errors.name} helperText={errors.name} fullWidth size="small" />
                </Grid>
                <Grid item xs={12} sm={6}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>Correo Electrónico</Typography>
                    <TextField required type="email" name="email" value={data.email} onChange={handleChange} error={!!errors.email} helperText={errors.email} fullWidth size="small" />
                </Grid>
                <Grid item xs={12} sm={6}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>Cédula</Typography>
                    <TextField required name="ci" value={data.ci} onChange={handleChange} error={!!errors.ci} helperText={errors.ci} fullWidth size="small" />
                </Grid>
                <Grid item xs={12} sm={6}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>ID UC (Opcional)</Typography>
                    <TextField name="id_uc" value={data.id_uc} onChange={handleChange} error={!!errors.id_uc} helperText={errors.id_uc} fullWidth size="small" />
                </Grid>
            </Grid>

            <Box display="flex" gap={2} mt={5}>
                <Button variant="contained" type="submit" disabled={processing}>
                    {isEdit ? 'Actualizar Docente' : 'Crear Docente'}
                </Button>
                {onCancel && <Button variant="outlined" color="inherit" onClick={onCancel}>Cancelar</Button>}
            </Box>
        </Box>
    );
}