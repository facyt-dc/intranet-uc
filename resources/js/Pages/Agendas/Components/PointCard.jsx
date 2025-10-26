import { useTranslation } from 'react-i18next';

import React from 'react';
import Paper from '@mui/material/Paper';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Divider from '@mui/material/Divider';
import Chip from '@mui/material/Chip';
import Tooltip from '@mui/material/Tooltip';
import IconButton from '@mui/material/IconButton';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import HowToVoteIcon from '@mui/icons-material/HowToVote';
import PersonIcon from '@mui/icons-material/Person';
import BlockIcon from '@mui/icons-material/Block';

import VoteForm from './VoteForm.jsx'; // Importamos el formulario de votación
import DeletePointDialog from './DeletePointDialog.jsx';
import ConclusionForm from './ConclusionForm.jsx';

export default function PointCard({ point, auth, agenda, onEdit, isAgendaOpen  }) {

    const { t } = useTranslation(['common', 'agenda']);

    const isDirector = auth.user.roles.some(role => role.name === 'director');
    
    // Verificamos si el usuario actual puede votar en este punto
    const canVote = point.votable_users.some(user => user.id === auth.user.id);
    
    // Verificamos si el usuario actual ya ha votado
    const userVote = point.votes.find(vote => vote.user.id === auth.user.id);

    const votes = point.votes || []; // Aseguramos que 'votes' sea un array
    const hasVotes = votes.length > 0; // Creamos una variable booleana para mayor claridad

    const votingIsComplete = votes.length >= point.min_votes_to_close;
    
    // Calculamos los resultados para el Director
    const voteCounts = point.votes.reduce((acc, vote) => {
        const optionName = vote.option.name;
        acc[optionName] = (acc[optionName] || 0) + 1;
        return acc;
    }, {});

    return (
        <Paper elevation={2} className="p-4">
            <Box className="flex justify-between items-start gap-4">
                {/* Información del Punto */}
                <Box>
                    <Typography variant="h6" component="h3">
                        {point.description}
                    </Typography>
                    <Typography variant="body2" color="text.secondary" className="flex items-center gap-1 mt-1">
                        <PersonIcon fontSize="small" /> Solicitado por: {point.requester.name}
                    </Typography>
                </Box>

                {/* Acciones para el Director */}
                {isDirector && isAgendaOpen && (
                    <Box>
                        <Tooltip title={hasVotes ? "No se puede editar, ya tiene votos" : "Editar Punto"}>
                            <span>
                                <IconButton size="small" onClick={onEdit} disabled={hasVotes}>
                                    {/* Cambiamos el ícono si está deshabilitado */}
                                    {hasVotes ? <BlockIcon fontSize="small" /> : <EditIcon />}
                                </IconButton>
                            </span>
                        </Tooltip>
                        
                        <DeletePointDialog agenda={agenda} point={point} />
                    </Box>
                )}
            </Box>

            <Divider className="my-3" />

            {/* Lógica de Votación */}
            {isDirector ? (
                // VISTA DEL DIRECTOR: Muestra los resultados
                <Box>
                    <Typography variant="subtitle2" gutterBottom>Resultados de la Votación ({point.votes.length} / {point.votable_users.length} votos)</Typography>
                    <Box className="flex gap-2 flex-wrap">
                        {Object.entries(voteCounts).map(([option, count]) => (
                            <Chip key={option} label={`${option}: ${count}`} variant="outlined" />
                        ))}
                    </Box>
                </Box>
            ) : (
                // VISTA DEL CONSEJERO
                <Box>
                    {canVote ? (
                        userVote ? (
                            <Typography color="primary" className="flex items-center gap-1">
                                <HowToVoteIcon /> Usted votó: <strong>{userVote.option.name}</strong>
                            </Typography>
                        ) : (
                            agenda.status !== 'Cerrado' ? (
                                <VoteForm point={point} />
                            ) : (
                                <Typography color="error">La votación está cerrada.</Typography>
                            )
                        )
                    ) : (
                        <Typography variant="body2" color="text.secondary">No tiene permiso para votar en este punto.</Typography>
                    )}
                </Box>
            )}

            {/* --- NUEVA SECCIÓN: Conclusión --- */}
            {/* Se muestra solo si la votación ha alcanzado el mínimo de votos */}
            {votingIsComplete && (
                <>
                    <Divider className="my-3" />
                    <Box>
                        <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 'bold' }}>
                            {t('Conclusion')}
                        </Typography>
                        
                        {/* El Director ve un formulario si el consejo está abierto */}
                        {isDirector && isAgendaOpen ? (
                            <ConclusionForm point={point} />
                        ) : (
                            // El Consejero o el Director (si el consejo está cerrado) ven solo el texto
                            <Typography variant="body2" sx={{ whiteSpace: 'pre-wrap' }}>
                                {point.conclusion || <i>{t('agenda:No conclusion has been added yet')}</i>}
                            </Typography>
                        )}
                    </Box>
                </>
            )}
        </Paper>
    );
}