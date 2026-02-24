import '../../app/styles/Dashboard.css';
import { Upload, FolderOpen, Search, Image, HardDrive, Clock, Award } from 'lucide-react';
import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import {
  PieChart,
  Pie,
  Cell,
  ResponsiveContainer,
  AreaChart,
  Area,
  XAxis,
  YAxis,
  Tooltip,
} from 'recharts';

export function DashboardPage() {
  const navigation = [
    { name: 'Загрузка', path: '/upload', icon: <Upload /> },
    { name: 'Архив', path: '/files', icon: <FolderOpen /> },
    { name: 'Поиск', path: '/search', icon: <Search /> },
  ];

  const stats = [
    { name: 'Общее кол-во фото', icon: <Image />, value: 1240 },
    { name: 'Занятое пространство', icon: <HardDrive />, value: '68 GB' },
    { name: 'Качество скорости', icon: <Award />, value: 'Отлично' },
  ];

  const storageData = [
    { name: 'Фото', value: 55, color: '#06b6d4' },
    { name: 'Видео', value: 30, color: '#2563eb' },
    { name: 'Другое', value: 15, color: '#6366f1' },
  ];

  const timelineData = [
    { month: 'Авг', storage: 20 },
    { month: 'Сен', storage: 35 },
    { month: 'Окт', storage: 50 },
    { month: 'Ноя', storage: 62 },
    { month: 'Дек', storage: 68 },
    { month: 'Янв', storage: 75 },
  ];

  return (
    <div className="dashboard-page">
      <div className="dashboard__content">
        {/* ---------- WELCOME ---------- */}
        <motion.div
          className="dashboard__welcome"
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
        >
          <h1>Панель управления</h1>
          <p>Добро пожаловать на вашу панель управления!</p>
        </motion.div>

        {/* ---------- NAVIGATION ---------- */}
        <div className="dashboard__nav">
          {navigation.map((item) => (
            <Link to={item.path} className="dashboard__nav-item">
              <div className="dashboard__nav-icon">{item.icon}</div>
              <h2>{item.name}</h2>
            </Link>
          ))}
        </div>

        {/* ---------- STATS ---------- */}
        <div className="dashboard__stats">
          {stats.map((item, index) => (
            <motion.div
              key={item.name}
              className="dashboard__stats-item"
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.2 + index * 0.1 }}
            >
              <div key={item.name} className="dashboard__stats-item">
                <div className="dashboard__stats__header">
                  <div className="dashboard__stats-icon">{item.icon}</div>
                  <div className="dashboard__stats-info">
                    <h2>{item.name}</h2>
                  </div>
                </div>
                <div className="dashboard__stats__body">
                  <div className="dashboard__stats__number">0</div>
                  <div className="dashboard__stats__comment">
                    {' '}
                    <p>comment</p>
                  </div>
                </div>
              </div>
            </motion.div>
          ))}
        </div>

        {/* ---------- STORAGE ---------- */}
        <div className="dashboard__storage">
          {/* Storage usage */}
          <motion.div
            className="dashboard__storage-usage"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.4 }}
          >
            <div className="dashboard__storage-usage__header">
              <HardDrive />
              <h2>Используемое хранилище</h2>
            </div>

            <ResponsiveContainer width="100%" height={220}>
              <PieChart>
                <Pie
                  data={storageData}
                  dataKey="value"
                  cx="50%"
                  cy="50%"
                  innerRadius={60}
                  outerRadius={90}
                  paddingAngle={4}
                >
                  {storageData.map((entry, index) => (
                    <Cell key={index} fill={entry.color} />
                  ))}
                </Pie>
              </PieChart>
            </ResponsiveContainer>

            <div className="storage-legend">
              {storageData.map((item) => (
                <div key={item.name} className="legend-item">
                  <span style={{ background: item.color }} />
                  {item.name}: {item.value}%
                </div>
              ))}
            </div>
          </motion.div>

          {/* Timeline */}
          <motion.div
            className="dashboard__storage-timeline"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.5 }}
          >
            <div className="dashboard__storage-timeline__header">
              <Clock />
              <h2>Таймлайн хранилища</h2>
            </div>

            <ResponsiveContainer
              width="100%"
              height={250}
              className="dashboard__storage-timeline__content"
            >
              <AreaChart data={timelineData}>
                <defs>
                  <linearGradient id="storageGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="#06b6d4" stopOpacity={0.8} />
                    <stop offset="95%" stopColor="#06b6d4" stopOpacity={0} />
                  </linearGradient>
                </defs>

                <XAxis dataKey="month" stroke="#94a3b8" />
                <YAxis stroke="#94a3b8" />
                <Tooltip />

                <Area
                  type="monotone"
                  dataKey="storage"
                  stroke="#06b6d4"
                  fill="url(#storageGradient)"
                />
              </AreaChart>
            </ResponsiveContainer>
          </motion.div>
        </div>

        {/* ---------- RECENT UPLOADS ---------- */}
        <motion.div
          className="dashboard__recent-uploads"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.6 }}
        >
          <div className="dashboard__recent-uploads__header">
            <h2>Последние сохранённые фото</h2>
            <p>Здесь будут отображаться ваши последние загрузки</p>
            {/* <Card></Card> */}
          </div>
        </motion.div>
      </div>
    </div>
  );
}
