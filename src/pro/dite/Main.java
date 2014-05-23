package pro.dite;

import org.eclipse.jgit.api.errors.GitAPIException;
import org.eclipse.jgit.diff.*;
import org.eclipse.jgit.lib.ObjectId;
import org.eclipse.jgit.revwalk.RevCommit;

import java.io.File;
import java.io.IOException;
import java.util.HashSet;
import java.util.Hashtable;

public class Main
{

    public static void main(String[] args) throws IOException, GitAPIException
    {
        // TODO do not require being in root
        String gitDir = System.getProperty("user.dir") + "/.git";

        final Hashtable<String, HashSet<String>> index = new Hashtable<String, HashSet<String>>();

        final File cacheFile = new File(gitDir + "/phpgit.bin");
        final Cache cache = Cache.loadFromFile(cacheFile);

        File repoDir = new File(gitDir);
        HeadWalker walker = new HeadWalker(repoDir)
        {
            @Override
            protected void onCommitDone(RevCommit commit)
            {
                CacheEntry entry = cache.entries.get(commit.getId().toString());
                if (entry != null)
                {
                    index.putAll(entry.index);
                }
            }

            @Override
            protected boolean shouldSkipCommit(RevCommit commit)
            {
                return cache.contains(commit);
            }

            @Override
            public void processFileDiff(RevCommit commit, EditList edits, PhpFile a, PhpFile b)
            {
                for (Edit edit : edits)
                {
                    if (edit.getType() == Edit.Type.REPLACE || edit.getType() == Edit.Type.DELETE)
                    {
                        CacheEntry entry = processEdit(a, edit.getBeginA(), edit.getEndA(), commit);
                        cache.entries.put(commit.getId().toString(), entry);
                    }
                    if (edit.getType() == Edit.Type.REPLACE || edit.getType() == Edit.Type.INSERT)
                    {
                        CacheEntry entry = processEdit(b, edit.getBeginB(), edit.getEndB(), commit);
                        cache.entries.put(commit.getId().toString(), entry);
                    }
                }
            }
        };

        walker.walk();
        cache.saveToFile(cacheFile);

        // build index from cache from all commits hit

        if (args.length >= 1)
        {
            String def = args[0];
            System.out.println("Authors of: " + def);
            if (!index.containsKey(def))
            {
                System.out.println("definition not found");
                return;
            }
            HashSet<String> authors = index.get(def);
            for (String author : authors)
            {
                System.out.println("\t" + author);
            }
        }
    }

    private static CacheEntry processEdit(PhpFile php, int begin, int end, RevCommit commit)
    {
        CacheEntry entry = new CacheEntry();
        for (int i = begin; i < end; ++i)
        {
            PhpFile.Line line = php.lines.get(i);
            if (line.isFunction())
            {
                String def = line.toStringFunction();
                if (!entry.index.containsKey(def))
                {
                    entry.index.put(def, new HashSet<String>());
                }
                entry.index.get(def).add(commit.getAuthorIdent().getEmailAddress());
            }

            String def = line.toString();
            if (!entry.index.containsKey(def))
            {
                entry.index.put(def, new HashSet<String>());
            }
            entry.index.get(def).add(commit.getAuthorIdent().getEmailAddress());
        }
        return entry;
    }

}
