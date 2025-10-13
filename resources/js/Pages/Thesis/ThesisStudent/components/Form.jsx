import React from "react";
import { useForm } from '@inertiajs/react';

import Box from '@mui/material/Box';
import TextField from '@mui/material/TextField';
import Grid from '@mui/material/Grid';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import FormControl from "@mui/material/FormControl";
import InputLabel from '@mui/material/InputLabel';
import Select from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';

export default function Form({ thesisStudent, statuses, routeName, method = 'post', onCancel }) {
    const isEdit = !!thesisStudent;

    const { data, setData, post, patch, processing, errors } = useForm({
        name: thesisStudent?.name ?? '',
        email: thesisStudent?.email ?? '',
        id_uc: thesisStudent?.id_uc ?? '',
        ci: thesisStudent?.ci ?? '',
        status_id: thesisStudent?.status_id ?? '',
    });

    const handleChange = (e) => {
        setData(e.target.name, e.target.value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (method === 'post') {
            post(route(routeName));
        } else {
            console.log('Updating thesis student:', thesisStudent?.id);
            patch(route(routeName, thesisStudent?.id));
        }
    };

    return (
        <Box
            component="form"
            sx={{ mt: 2 }}
            onSubmit={handleSubmit}
            id="thesisStudentForm"
            autoComplete="off"
        >
            <Grid container spacing={3}>
                <Grid item xs={12} sm={6}>
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                        Nombre
                    </Typography>
                    <TextField
                        required
                        name="name"
                        value={data.name}
                        onChange={handleChange}
                        error={!!errors.name}
                        helperText={errors.name}
                        fullWidth
                        size="small"
                        variant="outlined"
                    />
                </Grid>
                <Grid item xs={12} sm={6} >
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                        Correo electrónico
                    </Typography>
                    <TextField
                        required
                        name="email"
                        value={data.email}
                        onChange={handleChange}
                        error={!!errors.email}
                        helperText={errors.email}
                        fullWidth
                        size="small"
                        variant="outlined"
                    />
                </Grid>
                <Grid item xs={12} sm={6} >
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                        ID UC
                    </Typography>
                    <TextField
                        name="id_uc"
                        value={data.id_uc}
                        onChange={handleChange}
                        error={!!errors.id_uc}
                        helperText={errors.id_uc}
                        fullWidth
                        size="small"
                        variant="outlined"
                    />
                </Grid>
                <Grid item xs={12} sm={6} >
                    <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                        Cédula
                    </Typography>
                    <TextField
                        name="ci"
                        value={data.ci}
                        onChange={handleChange}
                        error={!!errors.ci}
                        helperText={errors.ci}
                        fullWidth
                        size="small"
                        variant="outlined"
                    />
                </Grid>

                 {isEdit && (
                    <Grid item xs={12}>
                        <Typography variant="subtitle2" color="text.secondary" gutterBottom>Estado del Estudiante</Typography>
                        <FormControl fullWidth size="small">
                            <InputLabel>Estado</InputLabel>
                            <Select
                                label="Estado"
                                name="status_id"
                                value={data.status_id}
                                onChange={handleChange}
                                error={!!errors.status_id}
                            >
                                {statuses?.map((status) => (
                                    <MenuItem key={status.id} value={status.id}>{status.name}</MenuItem>
                                ))}
                            </Select>
                            {errors.status_id && <Typography color="error" variant="caption">{errors.status_id}</Typography>}
                        </FormControl>
                    </Grid>
                )}
            </Grid>

            <Box display="flex" gap={2} mt={5}>
                <Button
                    variant="contained"
                    type="submit"
                    disabled={processing}
                >
                    {isEdit ? 'Actualizar' : 'Crear'}
                </Button>
                {onCancel &&
                    <Button variant="outlined" color="inherit" onClick={onCancel}>
                        Cancelar
                    </Button>
                }
            </Box>
        </Box>
    );
}
