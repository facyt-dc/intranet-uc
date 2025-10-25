import React from 'react';
import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import Box from '@mui/material/Box';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import CircularProgress from '@mui/material/CircularProgress';
import Typography from '@mui/material/Typography';

/**
 * Componente de formulario para añadir o editar la conclusión de un punto del consejo.
 *
 * @param {{ point: Object }} props El punto del consejo al que se le añadirá la conclusión.
 */
export default function ConclusionForm({ point }) {
    const { t } = useTranslation(['common']);

    // useForm se inicializa con la conclusión existente del punto.
    // Si no hay conclusión, se inicializa como una cadena vacía.
    const { data, setData, patch, processing, errors, recentlySuccessful } = useForm({
        conclusion: point.conclusion ?? '',
    });

    /**
     * Maneja el envío del formulario.
     * Envía una petición PATCH a la ruta 'points.conclusion.add'.
     *
     * @param {React.FormEvent} e El evento del formulario.
     */
    const handleSubmit = (e) => {
        e.preventDefault();
        patch(route('points.conclusion.add', point.id), {
            preserveScroll: true, // Evita que la página se desplace al inicio tras la petición
        });
    };

    return (
        <Box component="form" onSubmit={handleSubmit} sx={{ mt: 1 }}>
            <TextField
                id="conclusion"
                name="conclusion"
                label={t('write a conclusion')}
                multiline
                rows={4}
                fullWidth
                variant="outlined"
                value={data.conclusion}
                onChange={(e) => setData('conclusion', e.target.value)}
                error={!!errors.conclusion}
                helperText={errors.conclusion}
            />
            <Box sx={{ display: 'flex', justifyContent: 'flex-end', alignItems: 'center', mt: 2, gap: 2 }}>
                {/* Muestra un mensaje de "Guardado!" que se desvanece */}
                {recentlySuccessful && (
                    <Typography
                        variant="caption"
                        sx={{
                            color: 'success.main',
                            opacity: 1,
                            transition: 'opacity 0.5s ease-in-out',
                        }}
                    >
                        {t('saved')}!
                    </Typography>
                )}

                <Button
                    type="submit"
                    variant="contained"
                    size="small"
                    disabled={processing}
                    startIcon={processing ? <CircularProgress size={20} color="inherit" /> : null}
                >
                    {processing ? t('saving') : t('save conclusion')}
                </Button>
            </Box>
        </Box>
    );
}