import React from 'react';
import { useTranslation } from 'react-i18next';

// Componentes de Material-UI para una lista bien formateada
import Box from '@mui/material/Box';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemText from '@mui/material/ListItemText';
import ListItemAvatar from '@mui/material/ListItemAvatar';
import Avatar from '@mui/material/Avatar';
import Typography from '@mui/material/Typography';
import Divider from '@mui/material/Divider';

// Librería para formatear las fechas de forma amigable
import { formatRelative } from 'date-fns';
import { es } from 'date-fns/locale'; // Importamos el locale en español

// Recibe el array 'comments' como prop
export default function CommentsList({ comments }) {
    const { t } = useTranslation(['common', 'agenda']);

    // Si no hay comentarios, muestra un mensaje informativo.
    if (!comments || comments.length === 0) {
        return (
            <Typography variant="body2" color="text.secondary" sx={{ mt: 2, fontStyle: 'italic' }}>
                {t('agenda:No comments yet')}
            </Typography>
        );
    }

    return (
        <Box sx={{ mt: 3 }}>
            <Typography variant="subtitle1" sx={{ fontWeight: 'bold', mb: 1 }}>
                {t('agenda:Comments')}
            </Typography>
            <List sx={{ width: '100%', bgcolor: 'background.paper' }}>
                {comments.map((comment, index) => (
                    // Usamos React.Fragment para poder añadir un Divider entre los elementos
                    <React.Fragment key={comment.id}>
                        {/* No añadir un divisor antes del primer comentario */}
                        {index > 0 && <Divider variant="inset" component="li" />}
                        <ListItem alignItems="flex-start">
                            <ListItemAvatar>
                                {/* Mostramos un Avatar con la inicial del nombre del autor */}
                                <Avatar alt={comment.user.name}>
                                    {comment.user.name.charAt(0).toUpperCase()}
                                </Avatar>
                            </ListItemAvatar>
                            <ListItemText
                                primary={
                                    <React.Fragment>
                                        <Typography
                                            component="span"
                                            variant="body2"
                                            color="text.primary"
                                            sx={{ fontWeight: 'bold' }}
                                        >
                                            {comment.user.name}
                                        </Typography>
                                        <Typography component="span" variant="caption" sx={{ ml: 1, color: 'text.secondary' }}>
                                            {/* Formateamos la fecha para que sea relativa (ej: "ayer a las 15:30") */}
                                            {formatRelative(new Date(comment.created_at), new Date(), { locale: es })}
                                        </Typography>
                                    </React.Fragment>
                                }
                                secondary={
                                    <Typography
                                        component="p"
                                        variant="body2"
                                        color="text.primary"
                                        sx={{ whiteSpace: 'pre-wrap', mt: 0.5 }} // pre-wrap respeta los saltos de línea
                                    >
                                        {comment.body}
                                    </Typography>
                                }
                            />
                        </ListItem>
                    </React.Fragment>
                ))}
            </List>
        </Box>
    );
}