import React from 'react';
import { useForm } from '@inertiajs/react';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';

export default function VoteForm({ point }) {
    const { post, processing } = useForm({});

    const handleVote = (optionId) => {
        post(route('points.votes.store', { 
            point: point.id,
            voting_option_id: optionId 
        }), {
            preserveScroll: true // Evita que la página salte al inicio tras votar
        });
    };

    return (
        <Box className="flex items-center gap-2">
            <Typography variant="subtitle2">Emitir voto:</Typography>
            {point.voting_options.map(option => (
                <Button 
                    key={option.id}
                    variant="contained"
                    size="small"
                    disabled={processing}
                    onClick={() => handleVote(option.id)}
                >
                    {option.name}
                </Button>
            ))}
        </Box>
    );
}