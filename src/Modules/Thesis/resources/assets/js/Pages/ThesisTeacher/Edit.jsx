import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link } from "@inertiajs/react";
import React from "react";
import { useTranslation } from "react-i18next";
import ArrowCircleLeftRoundedIcon from "@mui/icons-material/ArrowCircleLeftRounded";
import IconButton from "@mui/material/IconButton";
import Tooltip from "@mui/material/Tooltip";
import Form from "./components/Form";
import Typography from "@mui/material/Typography";
import Paper from '@mui/material/Paper';

export default function ThesisTeacherEdit({ auth, teacher }) {
    const { t } = useTranslation(["common"]);
    return (
        <AdminLayout auth={auth}>
            <Head title={`${t("Editar Docente")}: ${teacher.name}`} />
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl text-gray-500">{`${t("Editar Docente")}: ${teacher.name}`}</h2>
                <Link href={route("thesisTeacher.index")}>
                    <Tooltip title={t("button.go back")}>
                        <IconButton size="large"><ArrowCircleLeftRoundedIcon fontSize="inherit" /></IconButton>
                    </Tooltip>
                </Link>
            </div>
            <Paper sx={{ p: 3 }}>
                <Form teacher={teacher} method="patch" routeName="thesisTeacher.update" />
            </Paper>
        </AdminLayout>
    );
}