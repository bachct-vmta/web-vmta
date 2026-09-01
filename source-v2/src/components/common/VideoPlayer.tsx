'use client';

import React, { useEffect, useRef } from 'react';
import Hls from 'hls.js';

interface VideoPlayerProps {
  src: string;
  poster?: string;
}

function getYouTubeEmbedUrl(url: string): string | null {
  if (!url) return null;
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
  const match = url.match(regExp);
  if (match && match[2] && match[2].length === 11) {
    return `https://www.youtube.com/embed/${match[2]}?autoplay=1&mute=1&loop=1&playlist=${match[2]}&controls=1`;
  }
  return null;
}

export const VideoPlayer: React.FC<VideoPlayerProps> = ({ src, poster }) => {
  const videoRef = useRef<HTMLVideoElement>(null);
  const youtubeEmbedUrl = getYouTubeEmbedUrl(src);

  useEffect(() => {
    if (youtubeEmbedUrl) return; // YouTube player uses iframe

    const video = videoRef.current;
    if (!video || !src) return;

    if (Hls.isSupported() && src.includes('.m3u8')) {
      const hls = new Hls();
      hls.loadSource(src);
      hls.attachMedia(video);
      return () => {
        hls.destroy();
      };
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
      video.src = src;
    } else {
      video.src = src;
    }
  }, [src, youtubeEmbedUrl]);

  if (youtubeEmbedUrl) {
    return (
      <div className="relative w-full aspect-video rounded-xl overflow-hidden shadow-lg border border-slate-200 bg-black">
        <iframe
          src={youtubeEmbedUrl}
          title="YouTube Video Player"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowFullScreen
          className="w-full h-full border-0"
        />
      </div>
    );
  }

  return (
    <video
      ref={videoRef}
      poster={poster || '/images/home/vision-mission/video-poster.webp'}
      muted
      autoPlay
      loop
      playsInline
      controls
      className="w-full rounded-xl shadow-lg border border-slate-200"
    />
  );
};
