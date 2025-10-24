import React, { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import Button from '@mui/material/Button';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogTitle from '@mui/material/DialogTitle';
import Box from '@mui/material/Box';
import TextField from '@mui/material/TextField';

// Recibe 'open', 'onClose' y la 'option' a editar (o null si es para crear)
export default function VotingOptionFormModal({ open, onClose, option }) {
    const { t } = useTranslation(['common']);
    const { data, setData, post, patch, processing, errors, reset } = useForm({
        name: '',
    });

    // useEffect se asegura de que el formulario se llene/resetee cuando cambia la prop 'option'
    useEffect(() => {
        if (option) {
            setData('name', option.name);
        } else {
            reset('name'); // Limpia el formulario para la creación
        }
    }, [option]);

    const handleSubmit = (e) => {
        e.preventDefault();
        const commonOptions = {
            onSuccess: () => onClose(),
            onError: () => console.log(errors),
        };

        if (option) {
            // Si hay una 'option', es una actualización (PATCH)
            patch(route('settings.voting-options.update', option.id), { ...commonOptions, data: { ...data, is_active: option.is_active } });
        } else {
            // Si no, es una creación (POST)
            post(route('settings.voting-options.store'), commonOptions);
        }
    };

    return (
        <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm">
            <DialogTitle>{option ? t('edit option') : t('create option')}</DialogTitle>
            <Box component="form" onSubmit={handleSubmit}>
                <DialogContent>
                    <TextField
                        autoFocus
                        margin="dense"
                        id="name"
                        label={t('Name')}
                        type="text"
                        fullWidth
                        variant="outlined"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        error={!!errors.name}
                        helperText={errors.name}
                        required
                    />
                </DialogContent>
                <DialogActions>
                    <Button onClick={onClose}>{t('button.cancel')}</Button>
                    <Button type="submit" variant="contained" disabled={processing}>
                        {option ? t('button.update') : t('button.create')}
                    </Button>
                </DialogActions>
            </Box>
        </Dialog>
    );
}