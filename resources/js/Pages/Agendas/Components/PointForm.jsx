import React, { useState, useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import FormControl from '@mui/material/FormControl';
import InputLabel from '@mui/material/InputLabel';
import Select from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';
import Checkbox from '@mui/material/Checkbox';
import ListItemText from '@mui/material/ListItemText';
import OutlinedInput from '@mui/material/OutlinedInput';
import FormHelperText from '@mui/material/FormHelperText';
import FormGroup from '@mui/material/FormGroup';


// Props:
// - agenda: El consejo al que se añadirá el punto.
// - counselors: Lista de consejeros para los selectores.
// - votingOptions: Lista de opciones de voto para el selector.
// - onSuccess: Función a llamar cuando el formulario se envía con éxito (para cerrar el modal).
// - onCancel: Función para cancelar y cerrar el modal.
export default function PointForm({ agenda, point, counselors, votingOptions, onSuccess, onCancel }) {
    const { t } = useTranslation(['common', 'agenda']);
    
    // El estado inicial se basa en si se proporciona un 'point'
    const { data, setData, post, patch, processing, errors, reset } = useForm({
        description: point?.description ?? '',
        requested_by_user_id: point?.requested_by_user_id ?? '',
        min_votes_to_close: point?.min_votes_to_close ?? 1,
        votable_users: point?.votable_users?.map(u => u.id) ?? [],
        available_options: point?.voting_options?.map(o => o.id) ?? [],
    });

    // --- LÓGICA DE VALIDACIÓN EN TIEMPO REAL ---
    const [votesError, setVotesError] = useState('');

    useEffect(() => {
        const minVotes = parseInt(data.min_votes_to_close, 10);
        const numVoters = data.votable_users.length;

        // Comparamos solo si hay votantes seleccionados
        if (numVoters > 0 && minVotes > numVoters) {
            setVotesError('No puede ser mayor que el número de votantes.');
        } else {
            setVotesError(''); // Limpiamos el error si la condición es válida
        }
    }, [data.min_votes_to_close, data.votable_users]);

    const handleSubmit = (e) => {
        e.preventDefault();
        // Lógica condicional: si hay un 'point', actualiza (PATCH); si no, crea (POST).
        if (point) {
            patch(route('points.update', { point: point.id }), {
                preserveScroll: true,
                onSuccess: () => onSuccess(),
            });
        } else {
            post(route('agendas.points.store', agenda.code), {
                preserveScroll: true,
                onSuccess: () => {
                    reset();
                    onSuccess();
                },
            });
        }
    };

    return (
        <Box component="form" onSubmit={handleSubmit} sx={{ mt: 1 }}>
            <FormGroup sx={{ display: "flex", gap: 3 }}>
                <TextField
                    label={t('Description')}
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                    error={!!errors.description}
                    helperText={errors.description}
                    multiline
                    rows={3}
                    fullWidth
                    required
                />
                <FormControl fullWidth error={!!errors.requested_by_user_id}>
                    <InputLabel id="requester-label">{t('agenda:Requested by')}</InputLabel>
                    <Select
                        labelId="requester-label"
                        value={data.requested_by_user_id}
                        onChange={(e) => setData('requested_by_user_id', e.target.value)}
                        label={t('agenda:Requested by')}
                        required
                    >
                        {counselors.map((c) => (
                            <MenuItem key={c.id} value={c.id}>{c.name}</MenuItem>
                        ))}
                    </Select>
                    {errors.requested_by_user_id && <FormHelperText>{errors.requested_by_user_id}</FormHelperText>}
                </FormControl>
                <TextField
                    label={t('agenda:Min votes to close')}
                    type="number"
                    value={data.min_votes_to_close}
                    onChange={(e) => setData('min_votes_to_close', e.target.value)}
                    error={!!errors.min_votes_to_close || !!votesError}
                    helperText={errors.min_votes_to_close || votesError}
                    fullWidth
                    required
                    InputProps={{ inputProps: { min: 1 } }}
                />
                <FormControl fullWidth error={!!errors.votable_users}>
                    <InputLabel id="votable-users-label">{t('agenda:Users who can vote')}</InputLabel>
                    <Select
                        labelId="votable-users-label"
                        multiple
                        value={data.votable_users}
                        onChange={(e) => setData('votable_users', e.target.value)}
                        input={<OutlinedInput label={t('agenda:Users who can vote')} />}
                        renderValue={(selected) => counselors.filter(c => selected.includes(c.id)).map(c => c.name).join(', ')}
                        required
                    >
                        {counselors.map((c) => (
                            <MenuItem key={c.id} value={c.id}>
                                <Checkbox checked={data.votable_users.includes(c.id)} />
                                <ListItemText primary={c.name} />
                            </MenuItem>
                        ))}
                    </Select>
                    {errors.votable_users && <FormHelperText>{errors.votable_users}</FormHelperText>}
                </FormControl>
                <FormControl fullWidth error={!!errors.available_options}>
                    <InputLabel id="voting-options-label">{t('agenda:Available voting options')}</InputLabel>
                    <Select
                        labelId="voting-options-label"
                        multiple
                        value={data.available_options}
                        onChange={(e) => setData('available_options', e.target.value)}
                        input={<OutlinedInput label={t('agenda:Available voting options')} />}
                        renderValue={(selected) => votingOptions.filter(o => selected.includes(o.id)).map(o => o.name).join(', ')}
                        required
                    >
                        {votingOptions.map((option) => (
                            <MenuItem key={option.id} value={option.id}>
                                <Checkbox checked={data.available_options.includes(option.id)} />
                                <ListItemText primary={option.name} />
                            </MenuItem>
                        ))}
                    </Select>
                    {errors.available_options && <FormHelperText>{errors.available_options}</FormHelperText>}
                </FormControl>
            </FormGroup>

            <Box sx={{ display: 'flex', justifyContent: 'flex-end', gap: 2, mt: 3 }}>
                <Button onClick={onCancel} color="secondary">
                    {t('button.cancel')}
                </Button>
                <Button type="submit" variant="contained" disabled={processing}>
                    {/* El texto del botón cambia dinámicamente */}
                    {point ? t('button.update') : t('button.create')}
                </Button>
            </Box>
        </Box>
    );
}