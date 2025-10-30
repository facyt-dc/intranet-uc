import React from 'react';
import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

// Componentes de Material-UI
import Box from '@mui/material/Box';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import CircularProgress from '@mui/material/CircularProgress';
import Typography from '@mui/material/Typography';

import SendIcon from '@mui/icons-material/Send';
import { usePage } from '@inertiajs/react';

// Recibe el 'point' al que pertenece el comentario
export default function CommentForm({ point }) {
    const { t } = useTranslation(['common', 'agenda']);
    const { auth } = usePage().props; // Obtenemos el usuario autenticado para mostrar su avatar (opcional)

    // Hook useForm de Inertia para manejar el estado y el envío
    const { data, setData, post, processing, errors, reset } = useForm({
        body: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        
        // Enviamos la petición POST a la ruta que creamos
        post(route('points.comments.store', point.id), {
            preserveScroll: true, // Evita que la página salte al inicio tras el envío
            onSuccess: () => {
                reset('body'); // Limpia el campo de texto después de un envío exitoso
            },
        });
    };

    return (
        <Box 
            component="form" 
            onSubmit={handleSubmit} 
            sx={{ 
                mt: 2, 
                display: 'flex', 
                alignItems: 'flex-start', 
                gap: 2 
            }}
        >

            <Box sx={{ width: '100%' }}>
                <TextField
                    label={t('agenda:Write a comment')}
                    variant="outlined"
                    fullWidth
                    multiline
                    rows={3}
                    value={data.body}
                    onChange={(e) => setData('body', e.target.value)}
                    error={!!errors.body} // Muestra un borde rojo si hay un error de validación
                    helperText={errors.body} // Muestra el mensaje de error del backend
                    disabled={processing}
                />
                <Box sx={{ display: 'flex', justifyContent: 'flex-end', mt: 1 }}>
                    <Button
                        type="submit"
                        variant="contained"
                        disabled={processing || data.body.trim() === ''} // Deshabilitado si está procesando o el campo está vacío
                        startIcon={processing ? <CircularProgress size={20} color="inherit" /> : <SendIcon />}
                    >
                        {t('agenda:Comment')}
                    </Button>
                </Box>
            </Box>
        </Box>
    );
}