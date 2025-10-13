import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link } from "@inertiajs/react";
import React from "react";
import ArrowCircleLeftRoundedIcon from "@mui/icons-material/ArrowCircleLeftRounded";
import IconButton from "@mui/material/IconButton";
import Tooltip from "@mui/material/Tooltip";
import Form from "./components/Form";
import { useTranslation } from "react-i18next";
import { Paper, Typography } from "@mui/material";

export default function ThesisTeacherCreate({ auth }) {
    const { t } = useTranslation(["common"]);
    return (
        <AdminLayout auth={auth}>
            <Head title={t("Crear Docente")} />
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl text-gray-500">{t("Crear Nuevo Docente")}</h2>
                <Link href={route("thesisTeacher.index")}>
                    <Tooltip title={t("button.go back")}>
                        <IconButton size="large"><ArrowCircleLeftRoundedIcon fontSize="inherit" /></IconButton>
                    </Tooltip>
                </Link>
            </div>
            <Paper sx={{ p: 3 }}>
                <Form method="post" routeName="thesisTeacher.store" />
            </Paper>
        </AdminLayout>
    );
}