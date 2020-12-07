# OCR
GCPのvisionAPIで法務局がくれる不動産情報のPDFファイルを読み取る


- /Keyというディレクトリの下にVisionApiKey.jsonを配置しないと死ぬ
- gcsにバケットを作成してからじゃないと死ぬ
- pdf圧縮解除ために、execでqpdf動かしてるからサーバにインストールしないと死ぬ