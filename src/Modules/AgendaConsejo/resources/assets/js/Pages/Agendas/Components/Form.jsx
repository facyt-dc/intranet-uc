import React from "react";
import { useForm } from '@inertiajs/react';

// Componentes de Material-UI necesarios
import Box from '@mui/material/Box';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import FormGroup from '@mui/material/FormGroup';
import FormLabel from '@mui/material/FormLabel';
import FormControl from '@mui/material/FormControl';
import Select from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';
import InputLabel from '@mui/material/InputLabel';
import Checkbox from '@mui/material/Checkbox';
import ListItemText from '@mui/material/ListItemText';
import OutlinedInput from '@mui/material/OutlinedInput';
import FormHelperText from '@mui/material/FormHelperText';

// Traducciones
import { useTranslation } from "react-i18next";

// Las props necesarias son 'agenda' (para editar), 'counselors' (la lista de consejeros),
// 'routeName' y 'method'.
export default function Form({ agenda, counselors, routeName, method }) {
    const { t } = useTranslation(["common"]);

    // Hook useForm de Inertia adaptado para los campos del consejo
    const { data, setData, post, patch, processing, errors } = useForm({
        name: agenda?.name ?? '',
        date: agenda?.date ? new Date(agenda.date).toISOString().slice(0, 10) : '',
        participants: agenda?.participants?.map(p => p.id) ?? [], // IDs de los participantes
    });

    // Manejador genérico para campos de texto y fecha
    const handleChange = (e) => {
        const key = e.target.id || e.target.name;
        const value = e.target.value;
        setData(data => ({
            ...data,
            [key]: value,
        }));
    };

    // Manejador específico para el selector múltiple de participantes
    const handleParticipantChange = (event) => {
        const {
          target: { value },
        } = event;
        setData(
          'participants',
          // En un selector múltiple, 'value' es un array
          typeof value === 'string' ? value.split(',') : value,
        );
      };

    // Manejador para enviar el formulario
    const handleSubmit = (e) => {
        e.preventDefault();
        if (method === 'post') {
            post(route(routeName));
        } else {
            patch(route(routeName, agenda.code)); // En la edición, usamos el código del consejo
        }
    };

    return (
        <Box
            component="form"
            sx={{ mt: 2 }}
            onSubmit={handleSubmit}
            id="agendaForm"
        >
            <FormGroup sx={{ display: "flex", gap: 3 }}>
                {/* Campo para el Nombre del Consejo */}
                <TextField
                    required
                    variant="outlined"
                    type="text"
                    id="name"
                    label={t("Name")}
                    value={data.name}
                    onChange={handleChange}
                    error={!!errors.name}
                    helperText={errors.name}
                    fullWidth
                />

                {/* Campo para la Fecha y Hora del Consejo */}
                <TextField
                    required
                    variant="outlined"
                    type="date"
                    id="date"
                    label={t("Date")}
                    value={data.date}
                    onChange={handleChange}
                    error={!!errors.date}
                    helperText={errors.date}
                    fullWidth
                    InputLabelProps={{
                        shrink: true, // Mantiene la etiqueta arriba
                    }}
                />

                {/* Selector Múltiple para los Participantes */}
                <FormControl fullWidth error={!!errors.participants}>
                    <InputLabel id="participants-multiple-checkbox-label">{t("Participants")}</InputLabel>
                    <Select
                        labelId="participants-multiple-checkbox-label"
                        id="participants"
                        name="participants" // Importante para el handleChange
                        multiple
                        value={data.participants}
                        onChange={handleParticipantChange}
                        input={<OutlinedInput label={t("Participants")} />}
                        renderValue={(selectedIds) => {
                            // Muestra los nombres de los consejeros seleccionados
                            return counselors
                                .filter(c => selectedIds.includes(c.id))
                                .map(c => c.name)
                                .join(', ');
                        }}
                    >
                        {counselors.map((counselor) => (
                            <MenuItem key={counselor.id} value={counselor.id}>
                                <Checkbox checked={data.participants.indexOf(counselor.id) > -1} />
                                <ListItemText primary={counselor.name} />
                            </MenuItem>
                        ))}
                    </Select>
                    {errors.participants && <FormHelperText>{errors.participants}</FormHelperText>}
                </FormControl>

            </FormGroup>

            <Button variant="contained" type="submit" disabled={processing} sx={{ mt: 3 }}>
                {agenda ? t('button.update') : t('button.create')}
            </Button>
        </Box>
    );
}